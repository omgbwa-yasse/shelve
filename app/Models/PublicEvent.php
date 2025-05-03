<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_events';

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'location',
        'is_online',
        'online_link',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_online' => 'boolean',
    ];

    public function registrations()
    {
        return $this->hasMany(PublicEventRegistration::class, 'event_id');
    }

    public function registeredUsers()
    {
        return $this->belongsToMany(PublicUser::class, 'public_event_registrations', 'event_id', 'user_id')
            ->withPivot('status', 'registered_at', 'notes')
            ->withTimestamps();
    }
}
