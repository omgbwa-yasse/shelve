<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingCategory extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'parent_id'
    ];

    /**
     * Les attributs à caster.
     *
     * @var array
     */
    protected $casts = [
        //
    ];

    /**
     * Relation avec la catégorie parent (structure hiérarchique)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(SettingCategory::class, 'parent_id');
    }

    /**
     * Relation avec les catégories enfants
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(SettingCategory::class, 'parent_id');
    }

    /**
     * Relation avec les paramètres de cette catégorie
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function settings()
    {
        return $this->hasMany(Setting::class, 'category_id');
    }

    /**
     * Récupère tous les paramètres de cette catégorie avec leurs valeurs pour un utilisateur
     *
     * @param int $userId
     * @param int|null $organisationId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSettingsWithValues($userId, $organisationId = null)
    {
        return $this->settings()
            ->with(['values' => function($query) use ($userId, $organisationId) {
                $query->where('user_id', $userId)
                    ->when($organisationId, function($q) use ($organisationId) {
                        $q->orWhere('organisation_id', $organisationId);
                    });
            }])
            ->get();
    }
}
