<?php

namespace App\Models;

use App\Enums\NotificationModule;
use App\Enums\NotificationPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organisation_id',
        'module',
        'event_type',
        'title',
        'message',
        'priority',
        'data',
        'action_url',
        'read_at',
        'scheduled_for',
    ];

    protected $casts = [
        'module' => NotificationModule::class,
        'priority' => NotificationPriority::class,
        'data' => 'array',
        'read_at' => 'datetime',
        'scheduled_for' => 'datetime',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    /**
     * Scopes pour filtrer les notifications
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForOrganisation(Builder $query, int $organisationId): Builder
    {
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeForModule(Builder $query, NotificationModule $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByPriority(Builder $query, NotificationPriority $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->whereNotNull('scheduled_for');
    }

    public function scopeReady(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('scheduled_for')
              ->orWhere('scheduled_for', '<=', now());
        });
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeOrderByImportance(Builder $query): Builder
    {
        return $query->orderByRaw("
            CASE priority
                WHEN 'high' THEN 3
                WHEN 'medium' THEN 2
                WHEN 'low' THEN 1
                ELSE 0
            END DESC
        ")->orderBy('created_at', 'desc');
    }

    /**
     * Méthodes utilitaires
     */
    public function markAsRead(): bool
    {
        return $this->update(['read_at' => now()]);
    }

    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function isScheduled(): bool
    {
        return !is_null($this->scheduled_for) && $this->scheduled_for > now();
    }

    public function isReady(): bool
    {
        return is_null($this->scheduled_for) || $this->scheduled_for <= now();
    }

    /**
     * Méthodes statiques pour créer des notifications
     */
    public static function createForUser(
        int $userId,
        NotificationModule $module,
        string $eventType,
        string $title,
        string $message,
        NotificationPriority $priority = NotificationPriority::MEDIUM,
        array $data = [],
        string $actionUrl = null,
        int $organisationId = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'organisation_id' => $organisationId,
            'module' => $module,
            'event_type' => $eventType,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'data' => $data,
            'action_url' => $actionUrl,
        ]);
    }

    public static function createForOrganisation(
        int $organisationId,
        NotificationModule $module,
        string $eventType,
        string $title,
        string $message,
        NotificationPriority $priority = NotificationPriority::MEDIUM,
        array $data = [],
        string $actionUrl = null
    ): void {
        // Récupérer tous les utilisateurs de l'organisation
        $organisation = Organisation::with('users')->find($organisationId);

        if ($organisation) {
            foreach ($organisation->users as $user) {
                self::createForUser(
                    $user->id,
                    $module,
                    $eventType,
                    $title,
                    $message,
                    $priority,
                    $data,
                    $actionUrl,
                    $organisationId
                );
            }
        }
    }

    /**
     * Méthodes pour les statistiques
     */
    public static function unreadCountForUser(int $userId): int
    {
        return self::forUser($userId)->unread()->count();
    }

    public static function unreadCountForOrganisation(int $organisationId): int
    {
        return self::forOrganisation($organisationId)->unread()->count();
    }
}
