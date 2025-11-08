<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordPeriodicalLoan extends Model
{
    use HasFactory;

    protected $table = 'record_periodical_loans';

    protected $fillable = [
        'issue_id',
        'borrower_id',
        'loan_date',
        'due_date',
        'return_date',
        'status',
        'late_fee',
        'fee_paid',
        'notes',
        'librarian_id',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'late_fee' => 'decimal:2',
        'fee_paid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(RecordPeriodicalIssue::class, 'issue_id');
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

    public function markAsReturned(): void
    {
        $this->update([
            'return_date' => now(),
            'status' => 'returned',
        ]);

        $this->issue->markAsReturned();
    }

    public function markAsLost(): void
    {
        $this->update(['status' => 'lost']);
        $this->issue->markAsLost();
    }

    public function payLateFee(): void
    {
        $this->update(['fee_paid' => true]);
    }

    /**
     * Static methods
     */
    public static function createLoan(RecordPeriodicalIssue $issue, User $borrower, int $loanDays = 7): self
    {
        $loan = self::create([
            'issue_id' => $issue->id,
            'borrower_id' => $borrower->id,
            'loan_date' => now(),
            'due_date' => now()->addDays($loanDays),
            'status' => 'active',
        ]);

        $issue->markAsOnLoan($loan);
        $issue->periodical->incrementLoanCount();

        return $loan;
    }
}
