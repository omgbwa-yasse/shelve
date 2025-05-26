<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ai_interaction_id',
        'rating',
        'comments',
        'was_helpful',
    ];

    protected $casts = [
        'rating' => 'integer',
        'was_helpful' => 'boolean',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interaction()
    {
        return $this->belongsTo(AiInteraction::class, 'ai_interaction_id');
    }
}
