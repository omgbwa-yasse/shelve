<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'channel',
        'is_active',
        'conditions',
    ];

    protected $casts = [
        'channel' => NotificationChannel::class,
        'is_active' => 'boolean',
        'conditions' => 'array',
    ];

    /**
     * L'utilisateur abonné à cette notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour filtrer par canal de notification
     */
    public function scopeViaChannel($query, NotificationChannel $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope pour les abonnements actifs uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour filtrer par type d'événement
     */
    public function scopeForEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }
}
