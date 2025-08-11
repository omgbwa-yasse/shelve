<?php

namespace App\Services\Llm;

use App\Models\LlmInteraction;
use App\Models\LlmDailyStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LlmMetricsService
{
    /**
     * Enregistre une interaction brute.
     */
    public function recordInteraction(array $data): LlmInteraction
    {
        // Calculs dérivés
        if (!isset($data['total_tokens'])) {
            $data['total_tokens'] = ($data['prompt_tokens'] ?? 0) + ($data['completion_tokens'] ?? 0);
        }
        return LlmInteraction::create($data);
    }

    /**
     * Agrège les statistiques journalières pour une date donnée (par défaut aujourd'hui).
     */
    public function aggregateDaily(?Carbon $day = null): void
    {
        $day = $day?->copy()->startOfDay() ?? now()->startOfDay();
        $start = $day->copy();
        $end = $day->copy()->endOfDay();

        $query = LlmInteraction::query()
            ->selectRaw('provider, model, source, user_id,
                COUNT(*) as requests_count,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count,
                SUM(CASE WHEN status != "success" THEN 1 ELSE 0 END) as error_count,
                SUM(prompt_tokens) as total_prompt_tokens,
                SUM(completion_tokens) as total_completion_tokens,
                SUM(total_tokens) as total_tokens,
                SUM(cost_microusd) as total_cost_microusd,
                AVG(latency_ms) as avg_latency_ms,
                MAX(latency_ms) as max_latency_ms')
            ->whereBetween('started_at', [$start, $end])
            ->groupBy('provider','model','source','user_id');

        $aggregates = $query->get();

        DB::transaction(function() use ($aggregates, $day) {
            foreach ($aggregates as $row) {
                LlmDailyStat::updateOrCreate([
                    'date' => $day->toDateString(),
                    'provider' => $row->provider,
                    'model' => $row->model,
                    'source' => $row->source,
                    'user_id' => $row->user_id,
                ], [
                    'requests_count' => $row->requests_count,
                    'success_count' => $row->success_count,
                    'error_count' => $row->error_count,
                    'total_prompt_tokens' => $row->total_prompt_tokens,
                    'total_completion_tokens' => $row->total_completion_tokens,
                    'total_tokens' => $row->total_tokens,
                    'total_cost_microusd' => $row->total_cost_microusd,
                    'avg_latency_ms' => (int) round($row->avg_latency_ms ?? 0),
                    'max_latency_ms' => (int) $row->max_latency_ms,
                ]);
            }
        });
    }

    /**
     * Retourne des stats consolidées pour le dashboard (période récente).
     */
    public function getDashboardSummary(int $days = 7): array
    {
        $since = now()->subDays($days);
        $base = LlmInteraction::where('started_at', '>=', $since);

        $total = (clone $base)->count();
        $success = (clone $base)->where('status', 'success')->count();
        $errors = $total - $success;

        $avgLatency = (clone $base)->avg('latency_ms') ?? 0;
        $totalTokens = (clone $base)->sum('total_tokens');
        $totalCostMicro = (clone $base)->sum('cost_microusd');

        // Top modèles
        $topModels = (clone $base)
            ->selectRaw('model, provider, COUNT(*) as c, SUM(total_tokens) as tokens')
            ->groupBy('model','provider')
            ->orderByDesc('c')
            ->limit(5)
            ->get();

        return [
            'period_days' => $days,
            'total_requests' => $total,
            'success_rate' => $total ? round($success * 100 / $total, 1) : 0,
            'error_rate' => $total ? round($errors * 100 / $total, 1) : 0,
            'avg_latency_ms' => (int) round($avgLatency),
            'total_tokens' => $totalTokens,
            'total_cost_microusd' => $totalCostMicro,
            'top_models' => $topModels,
        ];
    }

    /**
     * Statistiques détaillées (pour page statistics).
     */
    public function getDetailedStats(int $days = 30): array
    {
        $since = now()->subDays($days);
        $base = LlmInteraction::where('started_at', '>=', $since);

        $byDay = $base->clone()
            ->selectRaw('DATE(started_at) as d, COUNT(*) as requests, SUM(total_tokens) as tokens, SUM(cost_microusd) as cost, AVG(latency_ms) as avg_latency')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $byModel = $base->clone()
            ->selectRaw('provider, model, COUNT(*) as requests, SUM(total_tokens) as tokens, SUM(cost_microusd) as cost, AVG(latency_ms) as avg_latency, SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success_count')
            ->groupBy('provider','model')
            ->orderByDesc('requests')
            ->get();

        $statuses = $base->clone()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c','status');

        return [
            'period_days' => $days,
            'series_daily' => $byDay,
            'by_model' => $byModel,
            'status_breakdown' => $statuses,
        ];
    }
}
