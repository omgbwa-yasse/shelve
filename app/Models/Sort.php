<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sort extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * Valeurs autorisées pour le code
     */
    public const VALID_CODES = ['E', 'T', 'C'];

    /**
     * Boot method pour ajouter des validations
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($sort) {
            if (!in_array($sort->code, self::VALID_CODES)) {
                throw new \InvalidArgumentException(
                    'Le code doit être l\'une des valeurs suivantes: ' . implode(', ', self::VALID_CODES)
                );
            }
        });
    }

    /**
     * Obtenir la description du code
     */
    public function getCodeDescriptionAttribute()
    {
        return match($this->code) {
            'E' => 'Élimination',
            'T' => 'Tri/Transfert',
            'C' => 'Conservation',
            default => 'Inconnu'
        };
    }

    public function retentions()
    {
        return $this->hasMany(retention::class);
    }
}


