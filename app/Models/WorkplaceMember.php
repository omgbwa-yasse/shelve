<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkplaceMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'workplace_id',
        'user_id',
        'role',
        'can_create_folders',
        'can_create_documents',
        'can_delete',
        'can_share',
        'can_invite',
        'notify_on_new_content',
        'notify_on_mentions',
        'notify_on_updates',
        'invited_by',
        'joined_at',
        'last_activity_at',
    ];

    protected $casts = [
        'can_create_folders' => 'boolean',
        'can_create_documents' => 'boolean',
        'can_delete' => 'boolean',
        'can_share' => 'boolean',
        'can_invite' => 'boolean',
        'notify_on_new_content' => 'boolean',
        'notify_on_mentions' => 'boolean',
        'notify_on_updates' => 'boolean',
        'joined_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Scopes
     */
    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('joined_at');
    }

    /**
     * Helpers
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    public function canManageMembers(): bool
    {
        return $this->can_invite && $this->isAdmin();
    }
}
