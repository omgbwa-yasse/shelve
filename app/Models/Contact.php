<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
        'label',
        'notes',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    public function organisations()
    {
        return $this->belongsToMany(Organisation::class, 'organisation_contact', 'contact_id', 'organisation_id');
    }
}
