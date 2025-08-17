<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PromptTransactionService
{
    /**
     * Start a prompt transaction and return its ID.
     */
    public function start(array $data): int
    {
        $now = Carbon::now();
        $insert = [
            'prompt_id' => $data['prompt_id'] ?? null,
            'started_at' => $now,
            'finished_at' => null,
            'model' => $data['model'] ?? null,
            'model_provider' => $data['model_provider'] ?? null,
            'organisation_id' => $data['organisation_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'entity' => $data['entity'],
            'entity_ids' => !empty($data['entity_ids']) ? json_encode(array_values($data['entity_ids'])) : null,
            'status' => 'started',
            'tokens_input' => null,
            'tokens_output' => null,
            'error_message' => null,
            'latency_ms' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        return (int) DB::table('prompt_transactions')->insertGetId($insert);
    }

    /**
     * Mark transaction as succeeded and fill metrics.
     */
    public function finishSuccess(int $id, array $meta = []): void
    {
        $now = Carbon::now();
        DB::table('prompt_transactions')
            ->where('id', $id)
            ->update([
                'finished_at' => $now,
                'status' => 'succeeded',
                'tokens_input' => $meta['tokens_input'] ?? null,
                'tokens_output' => $meta['tokens_output'] ?? null,
                'latency_ms' => $meta['latency_ms'] ?? null,
                'updated_at' => $now,
            ]);
    }

    /**
     * Mark transaction as failed and record error.
     */
    public function finishFailure(int $id, string $errorMessage, ?int $latencyMs = null): void
    {
        $now = Carbon::now();
        DB::table('prompt_transactions')
            ->where('id', $id)
            ->update([
                'finished_at' => $now,
                'status' => 'failed',
                'error_message' => mb_substr($errorMessage, 0, 65000),
                'latency_ms' => $latencyMs,
                'updated_at' => $now,
            ]);
    }
}
