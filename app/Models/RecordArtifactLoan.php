<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordArtifactLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'artifact_id',
        'borrower_name',
        'borrower_contact',
        'borrower_address',
        'loan_date',
        'return_date',
        'actual_return_date',
        'status',
        'conditions',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'return_date' => 'date',
        'actual_return_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(RecordArtifact::class, 'artifact_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('status', 'active')
                  ->where('return_date', '<', now());
            });
    }

    /**
     * Accessors
     */
    public function getDaysOverdueAttribute(): ?int
    {
        if ($this->status === 'returned' || !$this->return_date) {
            return null;
        }

        $now = now();
        if ($now->lessThan($this->return_date)) {
            return 0;
        }

        return $now->diffInDays($this->return_date);
    }

    /**
     * Business logic methods
     */
    public function approve(User $user): void
    {
        $this->update([
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    public function extend(string $newReturnDate, ?string $notes = null): void
    {
        $this->update([
            'return_date' => $newReturnDate,
            'status' => 'extended',
            'notes' => $notes ? ($this->notes . "\n" . $notes) : $this->notes,
        ]);
    }

    public function markAsOverdue(): void
    {
        $this->update(['status' => 'overdue']);
    }

    public function return(?string $notes = null): void
    {
        $this->update([
            'actual_return_date' => now(),
            'status' => 'returned',
            'notes' => $notes ? ($this->notes . "\n" . $notes) : $this->notes,
        ]);

        $this->artifact->update(['is_on_loan' => false]);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active'
            && $this->return_date
            && now()->greaterThan($this->return_date);
    }
}
