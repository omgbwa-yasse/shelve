<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordArtifactExhibition extends Model
{
    use HasFactory;

    protected $fillable = [
        'artifact_id',
        'exhibition_name',
        'venue',
        'description',
        'start_date',
        'end_date',
        'is_current',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Relations
     */
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(RecordArtifact::class, 'artifact_id');
    }

    /**
     * Scopes
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopePast($query)
    {
        return $query->where('end_date', '<', now())
            ->orWhere('is_current', false);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Accessors
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Business logic methods
     */
    public function markAsCurrent(): void
    {
        $this->update(['is_current' => true]);
        $this->artifact->update(['is_on_display' => true]);
    }

    public function markAsEnded(): void
    {
        $this->update(['is_current' => false]);

        // Check if artifact has other current exhibitions
        if (!$this->artifact->exhibitions()->where('is_current', true)->exists()) {
            $this->artifact->update(['is_on_display' => false]);
        }
    }
}
