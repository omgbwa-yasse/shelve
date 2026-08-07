<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

}
