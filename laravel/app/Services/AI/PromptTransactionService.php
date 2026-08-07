<?php

namespace App\Services\AI;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PromptTransactionService
{
    private static ?array $ptColumns = null;
    /** @var array<string, array<string>> */
    private static array $enumCache = [];
    /** @var array<string, array{default: mixed, nullable: bool, data_type: string|null, column_type: string|null, extra: string|null}> */
    private static array $colMetaCache = [];
    /** @var array<string,bool> */
    private static array $autoIncCache = [];

    private function getColumns(): array
    {
        if (self::$ptColumns === null) {
            try {
                self::$ptColumns = Schema::getColumnListing('prompt_transactions');
            } catch (\Throwable $ex) {
                Log::debug('PromptTransactionService: getColumns failed', ['error' => $ex->getMessage()]);
                self::$ptColumns = [];
            }
        }
        return self::$ptColumns;
    }

    private function filterColumns(array $data): array
    {
        $cols = $this->getColumns();
        if (!empty($cols)) {
            return array_intersect_key($data, array_flip($cols));
        }
        // Fallback: probe each key to see if the column exists to avoid inserting unknown columns
        $filtered = [];
        foreach ($data as $k => $v) {
            try {
                if (Schema::hasColumn('prompt_transactions', $k)) {
                    $filtered[$k] = $v;
                }
            } catch (\Throwable $ex) {
                Log::debug('PromptTransactionService: filterColumns failed', ['error' => $ex->getMessage()]);
                // ignore and skip column
            }
        }
        // If nothing could be verified (e.g., permissions), return minimal safe subset
        return empty($filtered) ? $data : $filtered;
    }

    private function getEnumValues(string $table, string $column): array
    {
        $key = strtolower($table . '.' . $column);
        if (isset(self::$enumCache[$key])) {
            return self::$enumCache[$key];
        }
        try {
            $rows = DB::select(
                'SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
                [$table, $column]
            );
            if ($rows && isset($rows[0]->COLUMN_TYPE)) {
                $colType = (string) $rows[0]->COLUMN_TYPE;
                if (preg_match('/^enum\((.*)\)$/i', $colType, $m)) {
                    $list = str_getcsv($m[1], ',', "'");
                    return self::$enumCache[$key] = array_map('strval', $list);
                }
            }
        } catch (\Throwable $ex) {
            Log::debug('PromptTransactionService: getEnumValues failed', ['error' => $ex->getMessage()]);
        }
        return self::$enumCache[$key] = [];
    }

    private function mapStatusToAvailable(string $desired): ?string
    {
        $allowed = array_map('strtolower', $this->getEnumValues('prompt_transactions', 'status'));
        if (empty($allowed)) { return null; }

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

    private function getColumnMeta(string $table, string $column): array
    {
        $key = strtolower($table . '.' . $column);
        if (isset(self::$colMetaCache[$key])) { return self::$colMetaCache[$key]; }
        $meta = ['default' => null, 'nullable' => true, 'data_type' => null, 'column_type' => null, 'extra' => null];
        try {
            $rows = DB::select(
                'SELECT COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, COLUMN_TYPE, EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
                [$table, $column]
            );
            if ($rows) {
                $r = $rows[0];
                $meta['default'] = $r->COLUMN_DEFAULT;
                $meta['nullable'] = strtoupper((string)$r->IS_NULLABLE) === 'YES';
                $meta['data_type'] = isset($r->DATA_TYPE) ? (string)$r->DATA_TYPE : null;
                $meta['column_type'] = isset($r->COLUMN_TYPE) ? (string)$r->COLUMN_TYPE : null;
                $meta['extra'] = isset($r->EXTRA) ? (string)$r->EXTRA : null;
            }
        } catch (\Throwable $ex) {
            Log::debug('PromptTransactionService: getColumnMeta failed', ['error' => $ex->getMessage()]);
        }
        return self::$colMetaCache[$key] = $meta;
    }

    private function hasAutoIncrementId(): bool
    {
        $key = 'prompt_transactions.id';
        if (array_key_exists($key, self::$autoIncCache)) { return self::$autoIncCache[$key]; }
        try {
            $meta = $this->getColumnMeta('prompt_transactions', 'id');
            $extra = strtolower((string)($meta['extra'] ?? ''));
            return self::$autoIncCache[$key] = (strpos($extra, 'auto_increment') !== false);
        } catch (\Throwable $ex) {
            Log::debug('PromptTransactionService: hasAutoIncrementId probe failed', ['error' => $ex->getMessage()]);
            return self::$autoIncCache[$key] = true; // assume yes if unknown
        }
    }

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
            'entity' => $data['entity'] ?? null,
            'entity_ids' => !empty($data['entity_ids']) ? json_encode(array_values($data['entity_ids'])) : null,
            'tokens_input' => null,
            'tokens_output' => null,
            'error_message' => null,
            'latency_ms' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // If status column exists and has no default and is NOT NULL, set a compatible value
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

        // Pre-detect tables missing AUTO_INCREMENT and allocate id manually
        if (!$this->hasAutoIncrementId()) {
            return $this->insertWithManualId($insert);
        }

        // Try normal insert with auto-increment id
        try {
            return (int) DB::table('prompt_transactions')->insertGetId($insert);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            // Fallback for tables without AUTO_INCREMENT on id (various driver messages)
            if (
                stripos($msg, "Field 'id' doesn't have a default value") !== false
                || (stripos($msg, "doesn't have a default value") !== false && stripos($msg, "'id'") !== false)
                || stripos($msg, '1364') !== false
            ) {
                return $this->insertWithManualId($insert);
            }
            throw $e;
        }
    }

    private function insertWithManualId(array $insert): int
    {
        $lockName = 'prompt_transactions_id_lock';
        $gotLock = false;
        try {
            $res = DB::selectOne('SELECT GET_LOCK(?, 5) AS l', [$lockName]);
            $gotLock = isset($res->l) && ((int)$res->l) === 1;
        } catch (\Throwable $lockEx) {
            Log::debug('PromptTransactionService: GET_LOCK failed', ['error' => $lockEx->getMessage()]);
            $gotLock = false;
        }

        try {
            $attemptId = ((int) (DB::table('prompt_transactions')->max('id') ?? 0)) + 1;
            $insertWithId = $insert;
            for ($i = 0; $i < 5; $i++) {
                try {
                    $insertWithId['id'] = $attemptId;
                    DB::table('prompt_transactions')->insert($insertWithId);
                    return $attemptId;
                } catch (\Throwable $ie) {
                    $imsg = $ie->getMessage();
                    if (stripos($imsg, 'Duplicate entry') !== false || stripos($imsg, 'PRIMARY') !== false) {
                        $attemptId++;
                        continue;
                    }
                    throw $ie;
                }
            }
            // final fallback
            return (int) DB::table('prompt_transactions')->insertGetId($insert);
        } finally {
            if ($gotLock) {
                try { DB::select('SELECT RELEASE_LOCK(?) AS r', [$lockName]); }
                catch (\Throwable $unlockEx) { Log::debug('PromptTransactionService: RELEASE_LOCK failed', ['error' => $unlockEx->getMessage()]); }
            }
        }
    }

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
        $mapped = $this->mapStatusToAvailable('succeeded');
        if ($mapped !== null) { $update['status'] = $mapped; }
        $update = $this->filterColumns($update);
        DB::table('prompt_transactions')->where('id', $id)->update($update);
    }

    public function finishFailure(int $id, string $errorMessage, ?int $latencyMs = null): void
    {
        $now = Carbon::now();
        $update = [
            'finished_at' => $now,
            'error_message' => mb_substr($errorMessage, 0, 65000),
            'latency_ms' => $latencyMs,
            'updated_at' => $now,
        ];
        $mapped = $this->mapStatusToAvailable('failed');
        if ($mapped !== null) { $update['status'] = $mapped; }
        $update = $this->filterColumns($update);
        DB::table('prompt_transactions')->where('id', $id)->update($update);
    }
}
