<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordPeriodicalSubscription extends Model
{
    use HasFactory;

    protected $table = 'record_periodical_subscriptions';

    protected $fillable = [
        'periodical_id',
        'subscription_number',
        'start_date',
        'end_date',
        'subscription_type',
        'price',
        'currency',
        'supplier',
        'order_number',
        'status',
        'auto_renew',
        'renewal_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
        'auto_renew' => 'boolean',
        'renewal_date' => 'date',
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

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    /**
     * Accessors
     */
    public function getIsActiveAttribute(): bool
    {
        $now = now();
        return $this->status === 'active'
            && $now->between($this->start_date, $this->end_date);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date < now();
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        if ($this->is_expired) {
            return 0;
        }

        return now()->diffInDays($this->end_date, false);
    }

    public function getDurationInMonthsAttribute(): int
    {
        return $this->start_date->diffInMonths($this->end_date);
    }

    /**
     * Methods
     */
    public function renew(int $months = 12): void
    {
        $this->update([
            'start_date' => $this->end_date->addDay(),
            'end_date' => $this->end_date->addMonths($months),
            'renewal_date' => now(),
            'status' => 'active',
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function suspend(): void
    {
        $this->update(['status' => 'suspended']);
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }
}
