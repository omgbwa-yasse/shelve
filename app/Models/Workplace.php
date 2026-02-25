<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToOrganisation;

class Workplace extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganisation;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category_id',
        'icon',
        'color',
        'settings',
        'is_public',
        'allow_external_sharing',
        'max_members',
        'max_storage_mb',
        'members_count',
        'folders_count',
        'documents_count',
        'storage_used_bytes',
        'status',
        'start_date',
        'end_date',
        'archived_at',
        'organisation_id',
        'owner_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_public' => 'boolean',
        'allow_external_sharing' => 'boolean',
        'max_members' => 'integer',
        'max_storage_mb' => 'integer',
        'members_count' => 'integer',
        'folders_count' => 'integer',
        'documents_count' => 'integer',
        'storage_used_bytes' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(WorkplaceCategory::class, 'category_id');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(WorkplaceMember::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(WorkplaceFolder::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(WorkplaceDocument::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(WorkplaceActivity::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(WorkplaceInvitation::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(WorkplaceBookmark::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Accessors
     */
    public function getStorageUsedMbAttribute()
    {
        return round($this->storage_used_bytes / 1048576, 2);
    }

    public function getStoragePercentageAttribute()
    {
        if ($this->max_storage_mb == 0) return 0;
        return round(($this->storage_used_bytes / ($this->max_storage_mb * 1048576)) * 100, 2);
    }

    public function getIsFullAttribute()
    {
        return $this->members_count >= $this->max_members;
    }
}
