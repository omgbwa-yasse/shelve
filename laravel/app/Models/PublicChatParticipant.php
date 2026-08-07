<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicChatParticipant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_chat_participants';

    protected $fillable = [
        'chat_id',
        'user_id',
        'is_admin',
        'last_read_at',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'last_read_at' => 'datetime',
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
