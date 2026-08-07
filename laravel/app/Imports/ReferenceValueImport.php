<?php

namespace App\Imports;

use App\Models\ReferenceValue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Import en masse des valeurs d'un domaine (étape 7).
 *
 * Gabarit attendu (1re ligne = en-têtes) : code | value | description | active
 * Toute ligne invalide est rapportée nommément dans `getSummary()['errors']` —
 * aucune erreur silencieuse.
 */
class ReferenceValueImport implements ToCollection, WithHeadingRow, WithValidation
{
    private array $summary = [
        'created' => 0,
        'updated' => 0,
        'errors' => [],
    ];

    public function __construct(
        private $list,
        private ?int $userId = null,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $code = (string) ($row['code'] ?? '');
            $value = (string) ($row['value'] ?? '');
            $description = $row['description'] ?? null;
            $active = $this->parseActive($row['active'] ?? null);
            $lineNumber = $index + 2; // +1 en-tête +1 index 0-based

            if ($code === '' || $value === '') {
                $this->summary['errors'][] = "Ligne {$lineNumber} : code et valeur sont obligatoires.";

                continue;
            }

            $existing = ReferenceValue::where('list_id', $this->list->id)
                ->where('code', $code)
                ->first();

            if ($existing) {
                $existing->update([
                    'value' => $value,
                    'description' => $description,
                    'active' => $active,
                    'updated_by' => $this->userId,
                ]);
                $this->summary['updated']++;
            } else {
                ReferenceValue::create([
                    'list_id' => $this->list->id,
                    'code' => $code,
                    'value' => $value,
                    'description' => $description,
                    'active' => $active,
                    'created_by' => $this->userId,
                ]);
                $this->summary['created']++;
            }
        }
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'value' => ['required', 'string'],
        ];
    }

    private function parseActive($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'oui', 'o', 'yes', 'y', 'actif'], true);
    }

    public function getSummary(): array
    {
        return $this->summary;
    }
}
