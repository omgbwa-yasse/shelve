<?php

namespace App\Services;

use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import en masse de notices depuis un fichier Excel (gabarit : code | name |
 * description | type_id | start_date | end_date). Idempotent par code : si le
 * code existe, la notice est mise à jour. Rapport ligne par ligne (aucune
 * erreur silencieuse).
 */
class RecordsBulkImportService implements ToCollection, WithHeadingRow
{
    private array $report = ['created' => 0, 'updated' => 0, 'errors' => []];

    public function __construct(
        private int $organisationId,
        private ?int $userId = null,
    ) {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $code = trim((string) ($row['code'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));

            if ($code === '' || $name === '') {
                $this->report['errors'][] = "Ligne {$line} : code et nom sont obligatoires.";
                continue;
            }

            $type = null;
            if (!empty($row['type_id'])) {
                $type = RecordType::find($row['type_id']);
                if (!$type) {
                    $this->report['errors'][] = "Ligne {$line} : type_id {$row['type_id']} inconnu.";
                    continue;
                }
            }

            $data = [
                'code' => $code,
                'name' => $name,
                'description' => $row['description'] ?? null,
                'type_id' => $type?->id,
                'level_id' => RecordLevel::query()->value('id'),
                'status_id' => RecordStatus::query()->value('id'),
                'organisation_id' => $this->organisationId,
                'creator_id' => $this->userId,
                'start_date' => !empty($row['start_date']) ? $row['start_date'] : null,
                'end_date' => !empty($row['end_date']) ? $row['end_date'] : null,
            ];

            $existing = Record::withTrashed()->where('code', $code)->first();

            if ($existing) {
                $existing->update($data);
                $this->report['updated']++;
            } else {
                Record::create($data);
                $this->report['created']++;
            }
        }
    }

    public function import($file): array
    {
        \Maatwebsite\Excel\Facades\Excel::import($this, $file);

        return $this->report;
    }

    public function getReport(): array
    {
        return $this->report;
    }
}
