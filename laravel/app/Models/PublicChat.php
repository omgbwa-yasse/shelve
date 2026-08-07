<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicChat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_chats';

    protected $fillable = [
        'title',
        'is_group',
        'is_active',
    ];

    protected $casts = [
        'is_group' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function messages()
    {
        return $this->hasMany(PublicChatMessage::class, 'chat_id');
    }

    public function participants()
    {
        return $this->hasMany(PublicChatParticipant::class, 'chat_id');
    }

    public function users()
    {
        return $this->belongsToMany(PublicUser::class, 'public_chat_participants', 'chat_id', 'user_id')
            ->withPivot('is_admin', 'last_read_at')
            ->withTimestamps();
    }
}
