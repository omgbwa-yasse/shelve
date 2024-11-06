<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailPriority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',  // Ajouté d'après le schéma SQL
        'duration' // Ajouté d'après le schéma SQL
    ];

    public function mails()
    {
        return $this->hasMany(Mail::class); // 'priority_id' est implicite
    }
}
