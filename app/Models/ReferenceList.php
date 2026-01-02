<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceList extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the values for this reference list.
     */
    public function values(): HasMany
    {
        return $this->hasMany(ReferenceValue::class, 'list_id');
    }

    /**
     * Get the active values for this reference list.
     */
    public function activeValues(): HasMany
    {
        return $this->hasMany(ReferenceValue::class, 'list_id')
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('value');
    }

    /**
     * Get metadata definitions using this reference list.
     */
    public function metadataDefinitions(): HasMany
    {
        return $this->hasMany(MetadataDefinition::class);
    }

    /**
     * Get the user who created this list.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this list.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include active lists.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
