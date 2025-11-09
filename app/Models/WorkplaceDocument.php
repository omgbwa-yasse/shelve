<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkplaceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'workplace_id',
        'document_id',
        'shared_by',
        'shared_at',
        'share_note',
        'access_level',
        'is_featured',
        'views_count',
        'last_viewed_at',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'last_viewed_at' => 'datetime',
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

    public function document(): BelongsTo
    {
        return $this->belongsTo(RecordDigitalDocument::class, 'document_id');
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    /**
     * Scopes
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeMostViewed($query, $limit = 10)
    {
        return $query->orderBy('views_count', 'desc')->limit($limit);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('shared_at', '>=', now()->subDays($days));
    }

    /**
     * Helpers
     */
    public function incrementViews()
    {
        $this->increment('views_count');
        $this->update(['last_viewed_at' => now()]);
    }
}
