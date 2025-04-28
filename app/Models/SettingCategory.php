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
        'is_system'
    ];

    /**
     * Les attributs à caster.
     *
     * @var array
     */
    protected $casts = [
        'is_system' => 'boolean',
    ];

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
