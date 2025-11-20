<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordArtifact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'sub_category',
        'material',
        'technique',
        'height',
        'width',
        'depth',
        'weight',
        'dimensions_notes',
        'origin',
        'period',
        'date_start',
        'date_end',
        'date_precision',
        'author',
        'author_role',
        'author_birth_date',
        'author_death_date',
        'acquisition_method',
        'acquisition_date',
        'acquisition_price',
        'acquisition_source',
        'conservation_state',
        'conservation_notes',
        'last_conservation_check',
        'next_conservation_check',
        'current_location',
        'storage_location',
        'is_on_display',
        'is_on_loan',
        'estimated_value',
        'insurance_value',
        'valuation_date',
        'metadata',
        'access_level',
        'status',
        'creator_id',
        'organisation_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'depth' => 'decimal:2',
        'weight' => 'decimal:3',
        'date_start' => 'integer',
        'date_end' => 'integer',
        'author_birth_date' => 'date',
        'author_death_date' => 'date',
        'acquisition_date' => 'date',
        'acquisition_price' => 'decimal:2',
        'last_conservation_check' => 'date',
        'next_conservation_check' => 'date',
        'is_on_display' => 'boolean',
        'is_on_loan' => 'boolean',
        'estimated_value' => 'decimal:2',
        'insurance_value' => 'decimal:2',
        'valuation_date' => 'date',
    ];

    /**
     * Relations
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function exhibitions(): HasMany
    {
        return $this->hasMany(RecordArtifactExhibition::class, 'artifact_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(RecordArtifactLoan::class, 'artifact_id');
    }

    public function conditionReports(): HasMany
    {
        return $this->hasMany(RecordArtifactConditionReport::class, 'artifact_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachmentable');
    }

    public function dollies()
    {
        return $this->belongsToMany(Dolly::class, 'dolly_artifacts', 'artifact_id', 'dolly_id')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnDisplay($query)
    {
        return $query->where('is_on_display', true);
    }

    public function scopeOnLoan($query)
    {
        return $query->where('is_on_loan', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByConservationState($query, $state)
    {
        return $query->where('conservation_state', $state);
    }

    public function scopeByOrganisation($query, $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

    /**
     * Exhibition management methods
     */
    public function getCurrentExhibition()
    {
        return $this->exhibitions()
            ->where('is_current', true)
            ->first();
    }

    public function addToExhibition(array $data): RecordArtifactExhibition
    {
        $exhibition = $this->exhibitions()->create($data);

        if ($data['is_current'] ?? false) {
            $this->update(['is_on_display' => true]);
        }

        return $exhibition;
    }

    public function removeFromExhibition(): void
    {
        $this->exhibitions()
            ->where('is_current', true)
            ->update(['is_current' => false]);

        $this->update(['is_on_display' => false]);
    }

    /**
     * Loan management methods
     */
    public function getActiveLoan()
    {
        return $this->loans()
            ->where('status', 'active')
            ->first();
    }

    public function loanTo(array $data): RecordArtifactLoan
    {
        if ($this->is_on_loan) {
            throw new \Exception('Artifact is already on loan');
        }

        $loan = $this->loans()->create(array_merge($data, [
            'status' => 'active',
        ]));

        $this->update(['is_on_loan' => true]);

        return $loan;
    }

    public function returnFromLoan(?string $notes = null): void
    {
        $activeLoan = $this->getActiveLoan();

        if (!$activeLoan) {
            throw new \Exception('No active loan found');
        }

        $activeLoan->update([
            'actual_return_date' => now(),
            'status' => 'returned',
            'notes' => $notes ? ($activeLoan->notes . "\n" . $notes) : $activeLoan->notes,
        ]);

        $this->update(['is_on_loan' => false]);
    }

    /**
     * Conservation management methods
     */
    public function getLatestConditionReport()
    {
        return $this->conditionReports()
            ->latest('report_date')
            ->first();
    }

    public function addConditionReport(array $data): RecordArtifactConditionReport
    {
        $report = $this->conditionReports()->create($data);

        // Update artifact conservation state
        $this->update([
            'conservation_state' => $data['overall_condition'],
            'last_conservation_check' => $data['report_date'],
            'conservation_notes' => $data['observations'] ?? $this->conservation_notes,
        ]);

        return $report;
    }

    public function needsConservationCheck(): bool
    {
        if (!$this->next_conservation_check) {
            return false;
        }

        return now()->greaterThanOrEqualTo($this->next_conservation_check);
    }

    /**
     * Accessors
     */
    public function getDimensionsAttribute(): string
    {
        $parts = [];

        if ($this->height) {
            $parts[] = "H: {$this->height} cm";
        }
        if ($this->width) {
            $parts[] = "L: {$this->width} cm";
        }
        if ($this->depth) {
            $parts[] = "P: {$this->depth} cm";
        }

        return implode(' × ', $parts);
    }

    public function getDateRangeAttribute(): ?string
    {
        if (!$this->date_start && !$this->date_end) {
            return null;
        }

        if ($this->date_start === $this->date_end) {
            return (string) $this->date_start;
        }

        $precision = $this->date_precision ? $this->date_precision . ' ' : '';

        if ($this->date_start && $this->date_end) {
            return "{$precision}{$this->date_start}-{$this->date_end}";
        }

        return $precision . ($this->date_start ?? $this->date_end);
    }

    public function getEstimatedValueFormattedAttribute(): ?string
    {
        if (!$this->estimated_value) {
            return null;
        }

        return number_format($this->estimated_value, 2, ',', ' ') . ' €';
    }

    public function getInsuranceValueFormattedAttribute(): ?string
    {
        if (!$this->insurance_value) {
            return null;
        }

        return number_format($this->insurance_value, 2, ',', ' ') . ' €';
    }

    /**
     * Business logic methods
     */
    public function isAvailableForLoan(): bool
    {
        return !$this->is_on_loan
            && !$this->is_on_display
            && $this->status === 'active'
            && in_array($this->conservation_state, ['excellent', 'good']);
    }

    public function isFragile(): bool
    {
        return in_array($this->conservation_state, ['poor', 'critical']);
    }

    public function requiresUrgentConservation(): bool
    {
        return $this->conservation_state === 'critical';
    }
}
