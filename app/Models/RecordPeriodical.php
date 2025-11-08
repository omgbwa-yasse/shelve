<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordPeriodical extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'record_periodicals';

    protected $fillable = [
        'issn',
        'title',
        'subtitle',
        'abbreviated_title',
        'publisher',
        'place_of_publication',
        'start_year',
        'end_year',
        'dewey',
        'lcc',
        'subjects',
        'frequency',
        'frequency_details',
        'periodical_type',
        'format',
        'language',
        'is_subscribed',
        'subscription_start',
        'subscription_end',
        'subscription_price',
        'supplier',
        'description',
        'scope',
        'website',
        'editor_in_chief',
        'total_issues',
        'available_issues',
        'loan_count',
        'metadata',
        'access_level',
        'status',
        'creator_id',
        'organisation_id',
    ];

    protected $casts = [
        'subjects' => 'array',
        'metadata' => 'array',
        'start_year' => 'integer',
        'end_year' => 'integer',
        'is_subscribed' => 'boolean',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
        'subscription_price' => 'decimal:2',
        'total_issues' => 'integer',
        'available_issues' => 'integer',
        'loan_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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
        return $this->belongsTo(Organisation::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(RecordPeriodicalIssue::class, 'periodical_id')
            ->orderBy('publication_date', 'desc');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(RecordPeriodicalSubscription::class, 'periodical_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(RecordPeriodicalClaim::class, 'periodical_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSubscribed($query)
    {
        return $query->where('is_subscribed', true);
    }

    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('periodical_type', $type);
    }

    public function scopeBySubject($query, $subject)
    {
        return $query->whereJsonContains('subjects', $subject);
    }

    /**
     * Accessors
     */
    public function getFormattedIssnAttribute(): ?string
    {
        if (!$this->issn) {
            return null;
        }

        $issn = preg_replace('/[^0-9X]/', '', $this->issn);

        if (strlen($issn) === 8) {
            return substr($issn, 0, 4) . '-' . substr($issn, 4, 4);
        }

        return $this->issn;
    }

    public function getFullTitleAttribute(): string
    {
        return $this->subtitle
            ? $this->title . ': ' . $this->subtitle
            : $this->title;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getHasActiveSubscriptionAttribute(): bool
    {
        if (!$this->is_subscribed) {
            return false;
        }

        $now = now();
        return $this->subscription_start && $this->subscription_end
            && $now->between($this->subscription_start, $this->subscription_end);
    }

    public function getFrequencyLabelAttribute(): string
    {
        $frequencies = [
            'daily' => 'Quotidien',
            'weekly' => 'Hebdomadaire',
            'biweekly' => 'Bimensuel',
            'monthly' => 'Mensuel',
            'bimonthly' => 'Bimestriel',
            'quarterly' => 'Trimestriel',
            'semiannual' => 'Semestriel',
            'annual' => 'Annuel',
            'irregular' => 'Irrégulier',
        ];

        return $frequencies[$this->frequency] ?? $this->frequency;
    }

    /**
     * Methods
     */
    public function updateIssueStatistics(): void
    {
        $this->total_issues = $this->issues()->count();
        $this->available_issues = $this->issues()
            ->where('status', 'available')
            ->where('is_on_loan', false)
            ->count();
        $this->save();
    }

    public function incrementLoanCount(): void
    {
        $this->increment('loan_count');
    }

    public function getLatestIssue(): ?RecordPeriodicalIssue
    {
        return $this->issues()->first();
    }

    public function getIssuesByYear($year)
    {
        return $this->issues()
            ->where('publication_year', $year)
            ->get();
    }

    public function getActiveSubscription(): ?RecordPeriodicalSubscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->first();
    }

    public function getPendingClaims()
    {
        return $this->claims()
            ->where('status', 'pending')
            ->get();
    }

    public function isCeased(): bool
    {
        return $this->status === 'ceased' && $this->end_year !== null;
    }

    /**
     * Static methods
     */
    public static function mostLoaned($limit = 10)
    {
        return self::orderBy('loan_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function recentlyAdded($limit = 10)
    {
        return self::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
