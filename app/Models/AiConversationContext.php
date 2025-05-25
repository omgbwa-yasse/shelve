<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiConversationContext extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id', 'user_id', 'ai_model_id', 'context_data',
        'message_count', 'last_used_at', 'expires_at'
    ];

    protected $casts = [
        'context_data' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class);
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function touch()
    {
        $this->update(['last_used_at' => now()]);
    }
}