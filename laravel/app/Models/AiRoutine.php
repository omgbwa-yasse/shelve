<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Routine programmée de l'assistant IA : exécute un prompt ou un skill (D14,
 * `AiSkill`/`Prompt`) à intervalle régulier. `schedule_type` couvre les cas
 * réels (once/hourly/daily/weekly) plutôt qu'une expression cron générique —
 * voir `AiRoutinesRunDueCommand` pour l'exécution planifiée.
 */
class AiRoutine extends Model
{
    use HasFactory, BelongsToOrganisation;

    public const SCHEDULE_ONCE = 'once';
    public const SCHEDULE_HOURLY = 'hourly';
    public const SCHEDULE_DAILY = 'daily';
    public const SCHEDULE_WEEKLY = 'weekly';

    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'organisation_id',
        'created_by',
        'name',
        'description',
        'prompt_id',
        'skill_id',
        'schedule_type',
        'run_time',
        'day_of_week',
        'is_enabled',
        'next_run_at',
        'last_run_at',
        'last_status',
        'last_output',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'day_of_week' => 'integer',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(AiSkill::class, 'skill_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDue($query)
    {
        return $query->where('is_enabled', true)->where('next_run_at', '<=', now());
    }

    /** Calcule la prochaine échéance à partir de maintenant (création ou après exécution). */
    public function computeNextRunAt(?Carbon $from = null): ?Carbon
    {
        $from ??= now();

        return match ($this->schedule_type) {
            self::SCHEDULE_HOURLY => $from->copy()->addHour(),
            self::SCHEDULE_DAILY => $this->nextDailyOccurrence($from),
            self::SCHEDULE_WEEKLY => $this->nextWeeklyOccurrence($from),
            default => null, // 'once' : pas de récurrence, désactivée après exécution
        };
    }

    private function nextDailyOccurrence(Carbon $from): Carbon
    {
        $time = $this->run_time ? Carbon::parse($this->run_time) : Carbon::createFromTime(0, 0);
        $next = $from->copy()->setTime((int) $time->format('H'), (int) $time->format('i'));

        return $next->lessThanOrEqualTo($from) ? $next->addDay() : $next;
    }

    private function nextWeeklyOccurrence(Carbon $from): Carbon
    {
        $time = $this->run_time ? Carbon::parse($this->run_time) : Carbon::createFromTime(0, 0);
        $targetDay = $this->day_of_week ?? $from->dayOfWeek;

        $next = $from->copy()->setTime((int) $time->format('H'), (int) $time->format('i'));
        while ($next->dayOfWeek !== $targetDay || $next->lessThanOrEqualTo($from)) {
            $next->addDay();
        }

        return $next;
    }

    /** Enregistre le résultat d'une exécution et recalcule l'échéance suivante. */
    public function markRun(string $status, string $output): void
    {
        $this->update([
            'last_run_at' => now(),
            'last_status' => $status,
            'last_output' => $output,
            'next_run_at' => $this->computeNextRunAt(),
            'is_enabled' => $this->schedule_type === self::SCHEDULE_ONCE ? false : $this->is_enabled,
        ]);
    }
}
