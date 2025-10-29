<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_users';

    protected $fillable = [
        'name',
        'first_name',
        'phone1',
        'phone2',
        'address',
        'email',
        'password',
        'is_approved',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_approved' => 'boolean',
        'preferences' => 'array',
    ];

    public function news()
    {
        return $this->hasMany(PublicNews::class, 'user_id');
    }

    public function searchLogs()
    {
        return $this->hasMany(PublicSearchLog::class, 'user_id');
    }

    public function documentRequests()
    {
        return $this->hasMany(PublicDocumentRequest::class, 'user_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(PublicFeedback::class, 'user_id');
    }

    public function chatMessages()
    {
        return $this->hasMany(PublicChatMessage::class, 'user_id');
    }

    public function chatParticipations()
    {
        return $this->hasMany(PublicChatParticipant::class, 'user_id');
    }

    public function chats()
    {
        return $this->belongsToMany(PublicChat::class, 'public_chat_participants', 'user_id', 'chat_id')
            ->withPivot('is_admin', 'last_read_at')
            ->withTimestamps();
    }

    public function eventRegistrations()
    {
        return $this->hasMany(PublicEventRegistration::class, 'user_id');
    }

    public function events()
    {
        return $this->belongsToMany(PublicEvent::class, 'public_event_registrations', 'user_id', 'event_id')
            ->withPivot('status', 'registered_at', 'notes')
            ->withTimestamps();
    }
}
