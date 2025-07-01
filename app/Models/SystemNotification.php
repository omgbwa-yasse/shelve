<?php

namespace App\Models;

use App\Enums\NotificationPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'priority',
        'action_url',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'priority' => NotificationPriority::class,
    ];

    /**
     * L'utilisateur destinataire de cette notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marquer la notification comme lue
     */
    public function markAsRead()
    {
        $this->read_at = now();
        $this->save();

        return $this;
    }

    /**
     * Vérifier si la notification a été lue
     */
    public function isRead()
    {
        return $this->read_at !== null;
    }

    /**
     * Scope pour les notifications non lues
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope pour les notifications d'une certaine priorité
     */
    public function scopeWithPriority($query, NotificationPriority $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope pour les notifications d'un certain type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
