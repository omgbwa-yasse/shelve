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
 * Import en masse de notices depuis un fichier Excel. Étape intermédiaire :
 * choix des champs à importer (`$fields`) et valeurs par défaut des champs
 * obligatoires (`$defaults`). Idempotent par code. Rapport ligne par ligne.
 */
class RecordsBulkImportService implements ToCollection, WithHeadingRow
{
    private array $report = ['created' => 0, 'updated' => 0, 'errors' => []];

    public function __construct(
        private int $organisationId,
        private ?int $userId = null,
        private ?array $fields = null,
        private ?array $defaults = null,
    ) {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2;

            $code = $this->field($row, 'code', required: true, line: $line);
            $name = $this->field($row, 'name', required: true, line: $line);

            if ($code === null || $name === null) {
                continue;
            }

            $typeId = $this->field($row, 'type_id');
            $type = null;
            if ($typeId !== null && $typeId !== '') {
                $type = RecordType::find($typeId);
                if (!$type) {
                    $this->report['errors'][] = "Ligne {$line} : type_id {$typeId} inconnu.";
                    continue;
                }
            }

            $data = [
                'code' => $code,
                'name' => $name,
                'description' => $this->field($row, 'description'),
                'type_id' => $type?->id,
                'level_id' => RecordLevel::query()->value('id'),
                'status_id' => RecordStatus::query()->value('id'),
                'organisation_id' => $this->organisationId,
                'creator_id' => $this->userId,
                'start_date' => $this->field($row, 'start_date'),
                'end_date' => $this->field($row, 'end_date'),
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

    /**
     * Lit un champ depuis la ligne, en tenant compte des champs choisis et des
     * valeurs par défaut. Retourne null si absent (avec erreur si requis).
     */
    protected function field(Collection $row, string $field, bool $required = false, int $line = 0): ?string
    {
        // Champ non choisi → ignoré.
        if ($this->fields !== null && !in_array($field, $this->fields, true)) {
            return null;
        }

        $value = trim((string) ($row[$field] ?? ''));

        if ($value !== '') {
            return $value;
        }

        // Valeur par défaut (champs obligatoires ou réglés par l'utilisateur).
        if (isset($this->defaults[$field]) && $this->defaults[$field] !== '') {
            return trim((string) $this->defaults[$field]);
        }

        if ($required) {
            $this->report['errors'][] = "Ligne {$line} : le champ « {$field} » est obligatoire.";
            return null;
        }

        return null;
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
