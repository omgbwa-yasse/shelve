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
        'constraints'
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
     * Relation avec les valeurs personnalisées du paramètre
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function values()
    {
        return $this->hasMany(SettingValue::class);
    }
}
