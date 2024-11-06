<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTypology extends Model
{
    use HasFactory;

    protected $table = 'mail_typologies';

    protected $fillable = [
        'name',
        'description',
        'activity_id',
    ];

    public $timestamps = true; // Corrigé d'après le schéma SQL

    public function activity() // Nom de relation corrigé et convention de nommage respectée
    {
        return $this->belongsTo(Activity::class); // 'activity_id' est implicite
    }

    public function mails()
    {
        return $this->hasMany(Mail::class); // 'typology_id' est implicite
    }
}
