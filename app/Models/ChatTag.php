<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];


    public function messages()
    {
        return $this->belongsToMany(ChatMessage::class, 'chat_message_tags', 'tag_id', 'message_id')
            ->withPivot('relevance_score')
            ->withTimestamps();
    }


}
