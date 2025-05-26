<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_chat_messages';

    protected $fillable = [
        'chat_id',
        'user_id',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function chat()
    {
        return $this->belongsTo(PublicChat::class, 'chat_id');
    }

    public function user()
    {
        return $this->belongsTo(PublicUser::class, 'user_id');
    }
}
