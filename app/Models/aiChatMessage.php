<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_chat_id',
        'role',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    // Relations
    public function chat()
    {
        return $this->belongsTo(AiChat::class, 'ai_chat_id');
    }
}
