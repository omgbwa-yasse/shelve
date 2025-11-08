<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordBookCopy extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'record_book_copies';

    protected $fillable = [
        'book_id',
        'barcode',
        'call_number',
        'location',
        'shelf',
        'status',
        'condition',
        'acquisition_date',
        'acquisition_price',
        'acquisition_source',
        'is_on_loan',
        'current_loan_id',
        'notes',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'acquisition_price' => 'decimal:2',
        'is_on_loan' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(RecordBook::class, 'book_id');
    }

    public function currentLoan(): BelongsTo
    {
        return $this->belongsTo(RecordBookLoan::class, 'current_loan_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(RecordBookLoan::class, 'copy_id');
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

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }

    public function scopeByCondition($query, $condition)
    {
        return $query->where('condition', $condition);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Accessors
     */
    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([
            $this->location,
            $this->shelf,
            $this->call_number,
        ]);

        return implode(' - ', $parts);
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available' && !$this->is_on_loan;
    }

    public function getStatusLabelAttribute(): string
    {
        $statuses = [
            'available' => 'Disponible',
            'on_loan' => 'En prêt',
            'reserved' => 'Réservé',
            'in_repair' => 'En réparation',
            'lost' => 'Perdu',
            'withdrawn' => 'Retiré',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getConditionLabelAttribute(): string
    {
        $conditions = [
            'excellent' => 'Excellent',
            'good' => 'Bon',
            'fair' => 'Moyen',
            'poor' => 'Mauvais',
        ];

        return $conditions[$this->condition] ?? $this->condition;
    }

    /**
     * Methods
     */
    public function markAsOnLoan(RecordBookLoan $loan): void
    {
        $this->update([
            'is_on_loan' => true,
            'status' => 'on_loan',
            'current_loan_id' => $loan->id,
        ]);

        $this->book->updateCopyStatistics();
    }

    public function markAsReturned(): void
    {
        $this->update([
            'is_on_loan' => false,
            'status' => 'available',
            'current_loan_id' => null,
        ]);

        $this->book->updateCopyStatistics();
    }

    public function markAsReserved(): void
    {
        $this->update(['status' => 'reserved']);
        $this->book->updateCopyStatistics();
    }

    public function markAsLost(): void
    {
        $this->update([
            'status' => 'lost',
            'is_on_loan' => false,
            'current_loan_id' => null,
        ]);

        $this->book->updateCopyStatistics();
    }

    public function markAsWithdrawn(): void
    {
        $this->update([
            'status' => 'withdrawn',
            'is_on_loan' => false,
            'current_loan_id' => null,
        ]);

        $this->book->updateCopyStatistics();
    }

    public function sendToRepair(): void
    {
        $this->update(['status' => 'in_repair']);
        $this->book->updateCopyStatistics();
    }

    public function returnFromRepair(): void
    {
        $this->update(['status' => 'available']);
        $this->book->updateCopyStatistics();
    }

    public function getLoanHistory()
    {
        return $this->loans()
            ->orderBy('loan_date', 'desc')
            ->get();
    }

    public function getTotalLoans(): int
    {
        return $this->loans()->count();
    }
}
