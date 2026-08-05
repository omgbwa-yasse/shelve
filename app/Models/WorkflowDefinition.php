<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowDefinition extends Model
{
    use BelongsToOrganisation, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'bpmn_xml',
        'version',
        'status',
        'visibility',
        'allowed_user_ids',
        'allowed_role_ids',
        'organisation_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'allowed_user_ids' => 'array',
        'allowed_role_ids' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_PRIVATE = 'private';

    /**
     * L'utilisateur peut-il démarrer ce workflow ? (étape 10 — sécurité de démarrage)
     */
    public function canStart(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($this->visibility === self::VISIBILITY_PRIVATE) {
            $allowedUserIds = $this->allowed_user_ids ?? [];
            $allowedRoleIds = $this->allowed_role_ids ?? [];

            if (in_array($user->id, $allowedUserIds, true)) {
                return true;
            }

            $userRoleIds = $user->roles()->pluck('roles.id')->all();

            return ! empty(array_intersect($allowedRoleIds, $userRoleIds));
        }

        return $this->organisation_id === ($user->current_organisation_id ?? $user->organisation_id);
    }

    public $timestamps = false; // Using custom timestamp fields

    /**
     * Relations
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class, 'definition_id');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class, 'definition_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Accessors
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'draft';
    }
}
