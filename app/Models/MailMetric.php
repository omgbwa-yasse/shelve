<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'mail_id',
        'metric_date',
        'metric_type',
        'value',
        'unit',
        'metadata',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'value' => 'decimal:4',
        'metadata' => 'array',
    ];

    /**
     * Le courrier concerné par cette métrique
     */
    public function mail()
    {
        return $this->belongsTo(Mail::class);
    }

    /**
     * Scope pour filtrer par type de métrique
     */
    public function scopeOfType($query, $metricType)
    {
        return $query->where('metric_type', $metricType);
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopeInPeriod($query, $startDate, $endDate = null)
    {
        if ($endDate) {
            return $query->whereBetween('metric_date', [$startDate, $endDate]);
        }

        return $query->where('metric_date', '>=', $startDate);
    }

    /**
     * Scope pour obtenir les métriques de temps de traitement
     */
    public function scopeProcessingTime($query)
    {
        return $query->where('metric_type', 'processing_time');
    }

    /**
     * Scope pour obtenir les métriques de temps de réponse
     */
    public function scopeResponseTime($query)
    {
        return $query->where('metric_type', 'response_time');
    }
}
