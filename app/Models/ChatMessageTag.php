<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessageTag extends Model
{
    use HasFactory;

    protected $table = 'chat_message_tags';

    protected $fillable = [
        'message_id',
        'tag_id',
        'relevance_score'
    ];

    public $timestamps = false;

    protected $casts = [
        'relevance_score' => 'integer',
        'created_at' => 'datetime'
    ];

    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function tag()
    {
        return $this->belongsTo(ChatTag::class, 'tag_id');
    }

}
