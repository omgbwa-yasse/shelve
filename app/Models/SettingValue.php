<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingValue extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'setting_id',
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
        'value' => 'json',
    ];

    /**
     * Relation avec le paramètre associé
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function setting()
    {
        return $this->belongsTo(Setting::class);
    }

    /**
     * Relation avec l'utilisateur
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'organisation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
