<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\NotificationModule;
use App\Enums\NotificationAction;
use Illuminate\Support\Facades\Auth;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organisation_id',
        'module',
        'name',
        'message',
        'action',
        'related_entity_type',
        'related_entity_id',
        'is_read',
    ];

    protected $casts = [
        'module' => NotificationModule::class,
        'action' => NotificationAction::class,
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    // Scopes
    public function scopeForUser(Builder $query, ?int $userId = null): Builder
    {
        $userId = $userId ?? Auth::id();
        return $query->where('user_id', $userId);
    }

    public function scopeForOrganisation(Builder $query, ?int $organisationId = null): Builder
    {
        $organisationId = $organisationId ?? Auth::user()?->current_organisation_id;
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }

    public function scopeForModule(Builder $query, NotificationModule $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeForAction(Builder $query, NotificationAction $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Méthodes
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    public function markAsUnread(): void
    {
        $this->update(['is_read' => false]);
    }

    public function getRelatedEntity()
    {
        if (!$this->related_entity_type || !$this->related_entity_id) {
            return null;
        }

        $modelClass = 'App\\Models\\' . $this->related_entity_type;

        if (class_exists($modelClass)) {
            return $modelClass::find($this->related_entity_id);
        }

        return null;
    }

    public function getFormattedMessage(): string
    {
        if ($this->message) {
            return $this->message;
        }

        $entityName = $this->name;
        $action = $this->action->label();
        $module = $this->module->label();

        return "L'élément '{$entityName}' a été {$action} dans le module {$module}";
    }

    public function getTimeAgo(): string
    {
        return $this->created_at->diffForHumans();
    }

    // Méthodes statiques pour créer des notifications
    public static function createForUser(
        int $userId,
        NotificationModule $module,
        string $name,
        NotificationAction $action,
        ?string $message = null,
        ?string $relatedEntityType = null,
        ?int $relatedEntityId = null,
        ?int $organisationId = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'organisation_id' => $organisationId ?? Auth::user()?->current_organisation_id,
            'module' => $module,
            'name' => $name,
            'message' => $message,
            'action' => $action,
            'related_entity_type' => $relatedEntityType,
            'related_entity_id' => $relatedEntityId,
        ]);
    }

    public static function createForOrganisation(
        int $organisationId,
        NotificationModule $module,
        string $name,
        NotificationAction $action,
        ?string $message = null,
        ?string $relatedEntityType = null,
        ?int $relatedEntityId = null
    ): self {
        return self::create([
            'organisation_id' => $organisationId,
            'module' => $module,
            'name' => $name,
            'message' => $message,
            'action' => $action,
            'related_entity_type' => $relatedEntityType,
            'related_entity_id' => $relatedEntityId,
        ]);
    }
}
