<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordBookReservation extends Model
{
    use HasFactory;

    protected $table = 'record_book_reservations';

    protected $fillable = [
        'book_id',
        'user_id',
        'reservation_date',
        'expiry_date',
        'status',
        'copy_id',
        'notified_at',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'expiry_date' => 'date',
        'notified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(RecordBook::class, 'book_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(RecordBookCopy::class, 'copy_id');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'ready']);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Accessors
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function getStatusLabelAttribute(): string
    {
        $statuses = [
            'pending' => 'En attente',
            'ready' => 'Prêt',
            'fulfilled' => 'Complété',
            'cancelled' => 'Annulé',
            'expired' => 'Expiré',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Methods
     */
    public function markAsReady(RecordBookCopy $copy, int $expiryDays = 3): void
    {
        $this->update([
            'status' => 'ready',
            'copy_id' => $copy->id,
            'expiry_date' => now()->addDays($expiryDays),
            'notified_at' => now(),
        ]);

        $copy->markAsReserved();
    }

    public function markAsFulfilled(): void
    {
        $this->update(['status' => 'fulfilled']);
        $this->book->decrementReservationCount();
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
        $this->book->decrementReservationCount();

        if ($this->copy) {
            $this->copy->update(['status' => 'available']);
        }
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
        $this->book->decrementReservationCount();

        if ($this->copy) {
            $this->copy->update(['status' => 'available']);
        }
    }

    public function getQueuePosition(): int
    {
        return self::where('book_id', $this->book_id)
            ->where('status', 'pending')
            ->where('reservation_date', '<=', $this->reservation_date)
            ->where('id', '<=', $this->id)
            ->count();
    }

    /**
     * Static methods
     */
    public static function createReservation(RecordBook $book, User $user): self
    {
        $reservation = self::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'reservation_date' => now(),
            'status' => 'pending',
        ]);

        $book->incrementReservationCount();

        return $reservation;
    }

    public static function processQueue(RecordBook $book): void
    {
        $availableCopy = $book->getAvailableCopy();

        if (!$availableCopy) {
            return;
        }

        $nextReservation = self::where('book_id', $book->id)
            ->where('status', 'pending')
            ->orderBy('reservation_date')
            ->first();

        if ($nextReservation) {
            $nextReservation->markAsReady($availableCopy);
        }
    }
}
