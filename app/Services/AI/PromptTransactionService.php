<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class PromptTransactionService
{
    private static ?array $ptColumns = null;
    /**
     * Cache of enum values per table.column => [values]
     * @var array<string, array<string>>
     */
    private static array $enumCache = [];
    /**
     * Cache of column metadata per table.column
     * @var array<string, array{default: mixed, nullable: bool, data_type: string|null, column_type: string|null}>
     */
    private static array $colMetaCache = [];

    private function getColumns(): array
    {
        if (self::$ptColumns === null) {
            try {
                self::$ptColumns = Schema::getColumnListing('prompt_transactions');
            } catch (\Throwable) {
                self::$ptColumns = [];
            }
        }
        return self::$ptColumns;
    }

    private function filterColumns(array $data): array
    {
        $cols = $this->getColumns();
        if (empty($cols)) { return $data; }
        return array_intersect_key($data, array_flip($cols));
    }

    /**
     * Return enum values for a column if the DB supports it (MySQL). Otherwise returns [].
     */
    private function getEnumValues(string $table, string $column): array
    {
        $key = strtolower($table . '.' . $column);
        if (isset(self::$enumCache[$key])) {
            return self::$enumCache[$key];
        }
        try {
            $rows = DB::select(
                "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1",
                [$table, $column]
            );
            if ($rows && isset($rows[0]->COLUMN_TYPE)) {
                $colType = (string) $rows[0]->COLUMN_TYPE;
                if (preg_match('/^enum\((.*)\)$/i', $colType, $m)) {
                    // Parse enum('a','b','c') into ['a','b','c']
                    $list = str_getcsv($m[1], ',', "'");
                    self::$enumCache[$key] = array_map('strval', $list);
                    return self::$enumCache[$key];
                }
            }
        } catch (\Throwable) {
            // ignore
        }
        self::$enumCache[$key] = [];
        return [];
    }

    /**
     * Map a logical status (started|succeeded|failed|cancelled) to an available enum value.
     * Returns null if the column is not enum or no suitable mapping exists.
     */
    private function mapStatusToAvailable(string $desired): ?string
    {
        $allowed = array_map('strtolower', $this->getEnumValues('prompt_transactions', 'status'));
        if (empty($allowed)) { return null; }

        $candidates = [];
        switch (strtolower($desired)) {
            case 'started':
                $candidates = ['started','start','pending','in_progress','running'];
                break;
            case 'succeeded':
                $candidates = ['succeeded','success','ok','completed','done','complete'];
                break;
            case 'failed':
                $candidates = ['failed','failure','error','ko'];
                break;
            case 'cancelled':
            case 'canceled':
                $candidates = ['cancelled','canceled','cancel','aborted','stopped'];
                break;
            default:
                $candidates = [$desired];
        }

        foreach ($candidates as $c) {
            if (in_array(strtolower($c), $allowed, true)) {
                return $c;
            }
        }
        return null;
    }

    /**
     * Get column metadata (default, nullable, data_type, column_type) from INFORMATION_SCHEMA (MySQL).
     */
    private function getColumnMeta(string $table, string $column): array
    {
        $key = strtolower($table . '.' . $column);
        if (isset(self::$colMetaCache[$key])) { return self::$colMetaCache[$key]; }
        $meta = ['default' => null, 'nullable' => true, 'data_type' => null, 'column_type' => null];
        try {
            $rows = DB::select(
                "SELECT COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1",
                [$table, $column]
            );
            if ($rows) {
                $r = $rows[0];
                $meta['default'] = $r->COLUMN_DEFAULT;
                $meta['nullable'] = strtoupper((string)$r->IS_NULLABLE) === 'YES';
                $meta['data_type'] = isset($r->DATA_TYPE) ? (string)$r->DATA_TYPE : null;
                $meta['column_type'] = isset($r->COLUMN_TYPE) ? (string)$r->COLUMN_TYPE : null;
            }
        } catch (\Throwable) {
            // ignore
        }
        return self::$colMetaCache[$key] = $meta;
    }

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
            'tokens_input' => null,
            'tokens_output' => null,
            'error_message' => null,
            'latency_ms' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // If status column exists and has no default and is NOT NULL, we must set it to a compatible value
        $cols = $this->getColumns();
        if (in_array('status', $cols, true)) {
            $meta = $this->getColumnMeta('prompt_transactions', 'status');
            if ($meta['default'] === null && $meta['nullable'] === false) {
                $val = $this->mapStatusToAvailable('started');
                if ($val === null) {
                    $allowed = $this->getEnumValues('prompt_transactions', 'status');
                    $val = $allowed[0] ?? 'started';
                }
                $insert['status'] = $val;
            }
        }

        $insert = $this->filterColumns($insert);
        // Try normal insert with auto-increment id
        try {
            return (int) DB::table('prompt_transactions')->insertGetId($insert);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            // Fallback for tables without AUTO_INCREMENT on id
            if (stripos($msg, "Field 'id' doesn't have a default value") !== false) {
                $nextId = ((int) (DB::table('prompt_transactions')->max('id') ?? 0)) + 1;
                $insertWithId = $insert;
                $insertWithId['id'] = $nextId;
                DB::table('prompt_transactions')->insert($insertWithId);
                return $nextId;
            }
            throw $e;
        }
    }

    /**
     * Mark transaction as succeeded and fill metrics.
     */
    public function finishSuccess(int $id, array $meta = []): void
    {
        $now = Carbon::now();
        $update = [
            'finished_at' => $now,
            'tokens_input' => $meta['tokens_input'] ?? null,
            'tokens_output' => $meta['tokens_output'] ?? null,
            'latency_ms' => $meta['latency_ms'] ?? null,
            'updated_at' => $now,
        ];
        // Try to set a compatible status value if available
        $mapped = $this->mapStatusToAvailable('succeeded');
        if ($mapped !== null) {
            $update['status'] = $mapped;
        }
        $update = $this->filterColumns($update);
        DB::table('prompt_transactions')->where('id', $id)->update($update);
    }

    /**
     * Mark transaction as failed and record error.
     */
    public function finishFailure(int $id, string $errorMessage, ?int $latencyMs = null): void
    {
        $now = Carbon::now();
        $update = [
            'finished_at' => $now,
            'error_message' => mb_substr($errorMessage, 0, 65000),
            'latency_ms' => $latencyMs,
            'updated_at' => $now,
        ];
        // Try to set a compatible status value if available
        $mapped = $this->mapStatusToAvailable('failed');
        if ($mapped !== null) {
            $update['status'] = $mapped;
        }
        $update = $this->filterColumns($update);
        DB::table('prompt_transactions')->where('id', $id)->update($update);
    }
}
