<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicEventRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_event_registrations';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'registered_at',
        'notes',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(PublicEvent::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(PublicUser::class, 'user_id');
    }
}
