<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'name',
        'type',
        'default_value',
        'description',
        'is_system',
        'constraints',
        'user_id',
        'organisation_id',
        'value'
    ];

    /**
     * Les attributs à caster.
     *
     * @var array
     */
    protected $casts = [
        'default_value' => 'json',
        'constraints' => 'json',
        'is_system' => 'boolean',
        'value' => 'json',
    ];

    /**
     * Relation avec la catégorie du paramètre
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(SettingCategory::class, 'category_id');
    }

    /**
     * Relation avec l'utilisateur associé
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'organisation associée
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Obtient la valeur effective du paramètre
     * Retourne la valeur personnalisée si elle existe, sinon la valeur par défaut
     *
     * @return mixed
     */
    public function getEffectiveValue()
    {
        return $this->value !== null ? $this->value : $this->default_value;
    }

    /**
     * Vérifie si ce paramètre a une valeur personnalisée
     *
     * @return bool
     */
    public function hasCustomValue()
    {
        return $this->value !== null;
    }

    /**
     * Scope pour filtrer par utilisateur et organisation
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $userId
     * @param int|null $organisationId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUserAndOrganisation($query, $userId = null, $organisationId = null)
    {
        return $query->where(function ($q) use ($userId, $organisationId) {
            $q->where(function ($subQ) use ($userId, $organisationId) {
                // Paramètres spécifiques à l'utilisateur et l'organisation
                $subQ->where('user_id', $userId)
                     ->where('organisation_id', $organisationId);
            })->orWhere(function ($subQ) use ($userId) {
                // Paramètres spécifiques à l'utilisateur seulement
                $subQ->where('user_id', $userId)
                     ->whereNull('organisation_id');
            })->orWhere(function ($subQ) use ($organisationId) {
                // Paramètres spécifiques à l'organisation seulement
                $subQ->whereNull('user_id')
                     ->where('organisation_id', $organisationId);
            })->orWhere(function ($subQ) {
                // Paramètres globaux (sans valeurs personnalisées)
                $subQ->whereNull('user_id')
                     ->whereNull('organisation_id')
                     ->whereNull('value');
            });
        });
    }

    /**
     * Scope pour les paramètres système
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope pour les paramètres utilisateur
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUser($query)
    {
        return $query->where('is_system', false);
    }
}
