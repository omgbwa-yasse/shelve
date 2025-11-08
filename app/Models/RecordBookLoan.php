<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RecordBookLoan extends Model
{
    use HasFactory;

    protected $table = 'record_book_loans';

    protected $fillable = [
        'copy_id',
        'borrower_id',
        'loan_date',
        'due_date',
        'return_date',
        'status',
        'renewal_count',
        'late_fee',
        'fee_paid',
        'notes',
        'librarian_id',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'renewal_count' => 'integer',
        'late_fee' => 'decimal:2',
        'fee_paid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function copy(): BelongsTo
    {
        return $this->belongsTo(RecordBookCopy::class, 'copy_id');
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function librarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'librarian_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
            ->where('due_date', '<', now());
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    public function scopeDueToday($query)
    {
        return $query->where('status', 'active')
            ->whereDate('due_date', today());
    }

    public function scopeDueSoon($query, $days = 3)
    {
        return $query->where('status', 'active')
            ->whereBetween('due_date', [today(), today()->addDays($days)]);
    }

    /**
     * Accessors
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'active' && $this->due_date < now();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    public function getDaysUntilDueAttribute(): int
    {
        if ($this->status !== 'active') {
            return 0;
        }

        return now()->diffInDays($this->due_date, false);
    }

    public function getStatusLabelAttribute(): string
    {
        $statuses = [
            'active' => 'Actif',
            'returned' => 'Retourné',
            'overdue' => 'En retard',
            'renewed' => 'Renouvelé',
            'lost' => 'Perdu',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Methods
     */
    public function calculateLateFee(float $dailyRate = 0.50): float
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return $this->days_overdue * $dailyRate;
    }

    public function updateLateFee(float $dailyRate = 0.50): void
    {
        if ($this->is_overdue) {
            $this->late_fee = $this->calculateLateFee($dailyRate);
            $this->status = 'overdue';
            $this->save();
        }
    }

    public function renew(int $additionalDays = 14, int $maxRenewals = 3): bool
    {
        if ($this->renewal_count >= $maxRenewals) {
            return false;
        }

        if ($this->is_overdue) {
            return false;
        }

        $this->due_date = $this->due_date->addDays($additionalDays);
        $this->renewal_count++;
        $this->status = 'renewed';
        $this->save();

        return true;
    }

    public function markAsReturned(): void
    {
        $this->update([
            'return_date' => now(),
            'status' => 'returned',
        ]);

        $this->copy->markAsReturned();
    }

    public function markAsLost(): void
    {
        $this->update(['status' => 'lost']);
        $this->copy->markAsLost();
    }

    public function payLateFee(): void
    {
        $this->update(['fee_paid' => true]);
    }

    public function canRenew(int $maxRenewals = 3): bool
    {
        return $this->status === 'active'
            && $this->renewal_count < $maxRenewals
            && !$this->is_overdue;
    }

    public function getRemainingRenewals(int $maxRenewals = 3): int
    {
        return max(0, $maxRenewals - $this->renewal_count);
    }

    /**
     * Static methods
     */
    public static function createLoan(RecordBookCopy $copy, User $borrower, int $loanDays = 14): self
    {
        $loan = self::create([
            'copy_id' => $copy->id,
            'borrower_id' => $borrower->id,
            'loan_date' => now(),
            'due_date' => now()->addDays($loanDays),
            'status' => 'active',
        ]);

        $copy->markAsOnLoan($loan);
        $copy->book->incrementLoanCount();

        return $loan;
    }
}
