<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'provider',
        'version',
        'api_type',
        'capabilities',
        'is_active',
    ];

    protected $casts = [
        'capabilities' => 'json',
        'is_active' => 'boolean',
    ];

    // Relations
    public function interactions()
    {
        return $this->hasMany(AiInteraction::class);
    }

    public function chats()
    {
        return $this->hasMany(AiChat::class);
    }

    public function jobs()
    {
        return $this->hasMany(AiJob::class);
    }
}
