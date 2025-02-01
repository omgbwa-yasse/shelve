<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;


    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tokens_used'
    ];

    public $timestamps = false;

    protected $casts = [
        'role' => 'string',
        'created_at' => 'datetime'
    ];


    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }


    public function tags()
    {
        return $this->belongsToMany(ChatTag::class, 'chat_message_tags', 'message_id', 'tag_id')
            ->withPivot('relevance_score')
            ->withTimestamps();
    }
}
