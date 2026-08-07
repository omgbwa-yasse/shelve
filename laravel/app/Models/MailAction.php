<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailAction extends Model
{
    use HasFactory;

    protected $table = 'mail_actions';

    protected $fillable = [
        'name',
        'duration', // Ajouté d'après le schéma SQL
        'to_return',
        'description',
    ];

    protected $casts = [
        'to_return' => 'boolean',
    ];

    public function mails()
    {
        return $this->hasMany(Mail::class); // Relation avec la table 'mails'
    }
}
