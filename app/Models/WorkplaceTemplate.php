<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkplaceTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'category',
        'default_settings',
        'default_structure',
        'default_permissions',
        'is_active',
        'is_system',
        'usage_count',
        'display_order',
        'created_by',
    ];

    protected $casts = [
        'default_settings' => 'array',
        'default_structure' => 'array',
        'default_permissions' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'usage_count' => 'integer',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCustom($query)
    {
        return $query->where('is_system', false);
    }

    public function scopePopular($query, $limit = 5)
    {
        return $query->orderBy('usage_count', 'desc')->limit($limit);
    }

    /**
     * Helpers
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }
}
