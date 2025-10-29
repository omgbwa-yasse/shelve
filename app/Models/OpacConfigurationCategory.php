<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpacConfigurationCategory extends Model
{
    use HasFactory;

    protected $table = 'opac_configuration_categories';

    protected $fillable = [
        'name',
        'label',
        'description',
        'icon',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Relation avec les configurations de cette catégorie
     */
    public function configurations()
    {
        return $this->hasMany(OpacConfiguration::class, 'category_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    /**
     * Scope pour les catégories actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour ordonner par sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    /**
     * Récupère les catégories avec leurs configurations
     */
    public static function getWithConfigurations()
    {
        return self::active()
                   ->ordered()
                   ->with(['configurations' => function($query) {
                       $query->active()->ordered();
                   }])
                   ->get();
    }
}
