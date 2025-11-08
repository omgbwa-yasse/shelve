<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle pour les abonnements aux périodiques
 * Phase 8 - SpecKit
 */
class RecordPeriodicSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'periodic_id',
        'subscription_number',
        'start_date',
        'end_date',
        'auto_renewal',
        'cost',
        'currency',
        'payment_method',
        'invoice_number',
        'supplier',
        'supplier_contact',
        'subscription_type',
        'access_notes',
        'status',
        'notes',
        'responsible_user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renewal' => 'boolean',
        'cost' => 'decimal:2',
    ];

    /**
     * Relations
     */
    public function periodic(): BelongsTo
    {
        return $this->belongsTo(RecordPeriodic::class, 'periodic_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('subscription_type', $type);
    }

    /**
     * Accessors
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        $diff = now()->diffInDays($this->end_date, false);
        return $diff > 0 ? (int) $diff : 0;
    }

    public function getFormattedCostAttribute(): string
    {
        return number_format($this->cost, 2) . ' ' . $this->currency;
    }

    public function getDurationAttribute(): string
    {
        $months = $this->start_date->diffInMonths($this->end_date);

        if ($months === 12) {
            return '1 an';
        }

        if ($months > 12) {
            $years = floor($months / 12);
            $remaining = $months % 12;

            if ($remaining === 0) {
                return $years . ' an' . ($years > 1 ? 's' : '');
            }

            return $years . ' an' . ($years > 1 ? 's' : '') . ' et ' . $remaining . ' mois';
        }

        return $months . ' mois';
    }

    /**
     * Méthodes métier
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->end_date >= now();
    }

    public function isExpired(): bool
    {
        return $this->end_date < now();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->days_remaining <= $days;
    }

    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function renew(\DateTime $newEndDate, ?float $newCost = null): void
    {
        $updateData = [
            'start_date' => $this->end_date,
            'end_date' => $newEndDate,
            'status' => 'active',
        ];

        if ($newCost !== null) {
            $updateData['cost'] = $newCost;
        }

        $this->update($updateData);
    }
}
