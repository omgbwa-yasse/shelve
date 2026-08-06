<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use App\Traits\HasAttachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kpi extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganisation, HasAttachable;

    protected $fillable = [
        'code',
        'name',
        'description',
        'unit',
        'target_value',
        'direction',
        'frequency',
        'attachable_type',
        'attachable_id',
        'owner_id',
        'organisation_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(KpiMeasurement::class)->orderByDesc('measured_at');
    }

    public function latestMeasurement(): HasMany
    {
        return $this->measurements()->limit(1);
    }

    /**
     * Tendance entre les deux dernières mesures : 'up' | 'down' | 'flat' | null
     * (si moins de 2 mesures). Le sens "favorable" dépend de `direction`.
     */
    public function getTrendAttribute(): ?string
    {
        $last = $this->relationLoaded('measurements') ? $this->measurements->take(2) : $this->measurements()->limit(2)->get();

        if ($last->count() < 2) {
            return null;
        }

        $delta = (float) $last[0]->value - (float) $last[1]->value;

        if ($delta === 0.0) {
            return 'flat';
        }

        return $delta > 0 ? 'up' : 'down';
    }
}
