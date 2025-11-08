<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordPeriodicalClaim extends Model
{
    use HasFactory;

    protected $table = 'record_periodical_claims';

    protected $fillable = [
        'periodical_id',
        'issue_id',
        'claim_date',
        'claim_type',
        'description',
        'status',
        'resolution_date',
        'resolution_notes',
        'claimed_by',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'resolution_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function periodical(): BelongsTo
    {
        return $this->belongsTo(RecordPeriodical::class, 'periodical_id');
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(RecordPeriodicalIssue::class, 'issue_id');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'claimed_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('claim_type', $type);
    }

    public function scopeUnresolved($query)
    {
        return $query->whereIn('status', ['pending', 'sent']);
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => __('Pending'),
            'sent' => __('Sent to Supplier'),
            'resolved' => __('Resolved'),
            'cancelled' => __('Cancelled'),
            default => $this->status,
        };
    }

    public function getClaimTypeLabelAttribute(): string
    {
        return match($this->claim_type) {
            'missing' => __('Missing Issue'),
            'damaged' => __('Damaged Issue'),
            'late' => __('Late Delivery'),
            'wrong_issue' => __('Wrong Issue Received'),
            default => $this->claim_type,
        };
    }

    public function getIsResolvedAttribute(): bool
    {
        return $this->status === 'resolved';
    }

    public function getIsPendingAttribute(): bool
    {
        return in_array($this->status, ['pending', 'sent']);
    }

    public function getDaysSinceClaimAttribute(): int
    {
        return $this->claim_date->diffInDays(now());
    }

    /**
     * Methods
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
        ]);
    }

    public function markAsResolved(string $resolutionNotes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolution_date' => now(),
            'resolution_notes' => $resolutionNotes,
        ]);

        // Update the issue status if it was marked as missing/claimed
        if ($this->issue && $this->claim_type === 'missing') {
            $this->issue->markAsReceived();
        }
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Static methods
     */
    public static function createClaim(
        RecordPeriodical $periodical,
        ?RecordPeriodicalIssue $issue,
        string $claimType,
        string $description,
        int $claimedBy
    ): self {
        $claim = self::create([
            'periodical_id' => $periodical->id,
            'issue_id' => $issue?->id,
            'claim_date' => now(),
            'claim_type' => $claimType,
            'description' => $description,
            'status' => 'pending',
            'claimed_by' => $claimedBy,
        ]);

        // If claiming a missing issue, update its receipt status
        if ($issue && $claimType === 'missing') {
            $issue->update(['receipt_status' => 'claimed']);
        }

        return $claim;
    }
}
