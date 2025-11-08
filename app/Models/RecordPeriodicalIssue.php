<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordPeriodicalIssue extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'record_periodical_issues';

    protected $fillable = [
        'periodical_id',
        'volume',
        'issue_number',
        'special_issue',
        'publication_date',
        'publication_year',
        'publication_month',
        'pages',
        'cover_theme',
        'table_of_contents',
        'receipt_date',
        'receipt_status',
        'status',
        'is_on_loan',
        'current_loan_id',
        'location',
        'shelf',
        'barcode',
        'notes',
    ];

    protected $casts = [
        'volume' => 'integer',
        'issue_number' => 'integer',
        'publication_date' => 'date',
        'publication_year' => 'integer',
        'publication_month' => 'integer',
        'pages' => 'integer',
        'receipt_date' => 'date',
        'is_on_loan' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function periodical(): BelongsTo
    {
        return $this->belongsTo(RecordPeriodical::class, 'periodical_id');
    }

    public function currentLoan(): BelongsTo
    {
        return $this->belongsTo(RecordPeriodicalLoan::class, 'current_loan_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(RecordPeriodicalLoan::class, 'issue_id');
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where('is_on_loan', false);
    }

    public function scopeOnLoan($query)
    {
        return $query->where('is_on_loan', true);
    }

    public function scopeReceived($query)
    {
        return $query->where('receipt_status', 'received');
    }

    public function scopeExpected($query)
    {
        return $query->where('receipt_status', 'expected');
    }

    public function scopeMissing($query)
    {
        return $query->where('receipt_status', 'missing');
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('publication_year', $year);
    }

    /**
     * Accessors
     */
    public function getIssueIdentifierAttribute(): string
    {
        $parts = [];

        if ($this->volume) {
            $parts[] = 'Vol. ' . $this->volume;
        }

        if ($this->issue_number) {
            $parts[] = 'No. ' . $this->issue_number;
        }

        if ($this->special_issue) {
            $parts[] = $this->special_issue;
        }

        return implode(', ', $parts) ?: 'Sans numéro';
    }

    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([
            $this->location,
            $this->shelf,
        ]);

        return implode(' - ', $parts);
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available' && !$this->is_on_loan;
    }

    public function getIsLateAttribute(): bool
    {
        if ($this->receipt_status !== 'expected') {
            return false;
        }

        return $this->publication_date->addDays(30) < now();
    }

    /**
     * Methods
     */
    public function markAsReceived(): void
    {
        $this->update([
            'receipt_status' => 'received',
            'receipt_date' => now(),
            'status' => 'available',
        ]);

        $this->periodical->updateIssueStatistics();
    }

    public function markAsOnLoan(RecordPeriodicalLoan $loan): void
    {
        $this->update([
            'is_on_loan' => true,
            'status' => 'on_loan',
            'current_loan_id' => $loan->id,
        ]);

        $this->periodical->updateIssueStatistics();
    }

    public function markAsReturned(): void
    {
        $this->update([
            'is_on_loan' => false,
            'status' => 'available',
            'current_loan_id' => null,
        ]);

        $this->periodical->updateIssueStatistics();
    }

    public function markAsMissing(): void
    {
        $this->update(['receipt_status' => 'missing']);
    }

    public function markAsLost(): void
    {
        $this->update([
            'status' => 'lost',
            'is_on_loan' => false,
            'current_loan_id' => null,
        ]);

        $this->periodical->updateIssueStatistics();
    }

    public function getLoanHistory()
    {
        return $this->loans()
            ->orderBy('loan_date', 'desc')
            ->get();
    }
}
