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
     * Démarre une interaction (statut provisoire). Retourne le modèle créé.
     */
    public function start(array $data): LlmInteraction
    {
        $data = array_merge([
            'status' => $data['status'] ?? 'in_progress',
            'started_at' => $data['started_at'] ?? now(),
            'prompt_tokens' => $data['prompt_tokens'] ?? 0,
            'completion_tokens' => $data['completion_tokens'] ?? 0,
            'total_tokens' => $data['total_tokens'] ?? 0,
            'latency_ms' => 0,
            'cost_microusd' => $data['cost_microusd'] ?? 0,
            'source' => $data['source'] ?? 'mcp',
        ], $data);
        return LlmInteraction::create($data);
    }

    /**
     * Termine / met à jour une interaction commencée via start().
     * $result peut contenir: status, error_code, prompt_tokens, completion_tokens, total_tokens, cost_microusd, latency_ms, metadata
     */
    public function finish(LlmInteraction $interaction, array $result): LlmInteraction
    {
        // Calcul de la latence si non fournie
        if (!isset($result['latency_ms'])) {
            $result['latency_ms'] = (int) now()->diffInMilliseconds($interaction->started_at ?? now());
        }
        if (isset($result['prompt_tokens']) || isset($result['completion_tokens'])) {
            $pt = $result['prompt_tokens'] ?? $interaction->prompt_tokens;
            $ct = $result['completion_tokens'] ?? $interaction->completion_tokens;
            $result['total_tokens'] = $pt + $ct;
        }
        $result['completed_at'] = $result['completed_at'] ?? now();
        $interaction->fill($result);
        $interaction->save();
        return $interaction;
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

    /**
     * Série temporelle quotidienne (appuie sur llm_daily_stats si disponible, sinon interactions directes).
     */
    public function getTimeSeries(int $days = 30): array
    {
        $from = now()->startOfDay()->subDays($days - 1);
        // Essayer daily stats (plus léger)
        $daily = LlmDailyStat::query()
            ->where('date', '>=', $from->toDateString())
            ->selectRaw('date as d, SUM(requests_count) as requests, SUM(total_tokens) as tokens, SUM(total_cost_microusd) as cost_micro, AVG(avg_latency_ms) as avg_latency, SUM(success_count) as success, SUM(error_count) as errors')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Si pas assez de données daily, fallback interactions
        if ($daily->count() < $days / 2) {
            $daily = LlmInteraction::query()
                ->where('started_at', '>=', $from)
                ->selectRaw('DATE(started_at) as d, COUNT(*) as requests, SUM(total_tokens) as tokens, SUM(cost_microusd) as cost_micro, AVG(latency_ms) as avg_latency, SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as success, SUM(CASE WHEN status != "success" THEN 1 ELSE 0 END) as errors')
                ->groupBy('d')
                ->orderBy('d')
                ->get();
        }

        return $daily->map(function($row){
            $total = max(1, ($row->success + $row->errors));
            return [
                'date' => $row->d,
                'requests' => (int) $row->requests,
                'tokens' => (int) $row->tokens,
                'cost_microusd' => (int) $row->cost_micro,
                'avg_latency_ms' => (int) round($row->avg_latency),
                'success_rate' => round($row->success * 100 / $total, 1),
            ];
        })->all();
    }

    /**
     * Top des codes d'erreur (ou statuts != success)
     */
    public function getTopFailures(int $days = 30, int $limit = 5)
    {
        $since = now()->subDays($days);
        return LlmInteraction::query()
            ->where('started_at', '>=', $since)
            ->where('status', '!=', 'success')
            ->selectRaw('COALESCE(error_code, status) as code, COUNT(*) as c')
            ->groupBy('code')
            ->orderByDesc('c')
            ->limit($limit)
            ->get();
    }

    /** Format utilitaire coût (USD) */
    public function formatCost(int $micro): string
    {
        return '$' . number_format($micro / 1_000_000, 4);
    }
}
