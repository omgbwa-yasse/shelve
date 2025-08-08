<?php

namespace App\Imports;

use App\Models\Container;
use App\Models\Dolly;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\Activity;
use App\Models\Author;
use App\Models\ThesaurusConcept;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class RecordsImport implements ToModel, WithBatchInserts, WithChunkReading
{
    protected Dolly $dolly;
    protected array $mapping;
    protected bool $hasHeaders;
    protected bool $updateExisting;
    protected int $currentRow = 0;
    protected int $importedCount = 0;

    public function __construct(Dolly $dolly, array $mapping = [], bool $hasHeaders = true, bool $updateExisting = false)
    {
        $this->dolly = $dolly;
        $this->mapping = $mapping;
        $this->hasHeaders = $hasHeaders;
        $this->updateExisting = $updateExisting;
    }

    public function model(array $row)
    {
        $this->currentRow++;

        // Ignorer la première ligne si elle contient les en-têtes
        if ($this->hasHeaders && $this->currentRow === 1) {
            return null;
        }

        try {
            $mappedData = $this->mapRowData($row);

            if (!$this->validateRequiredFields($mappedData)) {
                Log::warning("Ligne {$this->currentRow} ignorée: champs requis manquants", $mappedData);
                return null;
            }

            $record = $this->createOrUpdateRecord($mappedData);

            if ($record) {
                $this->dolly->records()->attach($record->id);
                $this->processAuthors($record, $mappedData);
                $this->processTerms($record, $mappedData);
                $this->importedCount++;
            }

            return $record;
        } catch (\Exception $e) {
            Log::error("Erreur ligne {$this->currentRow}: " . $e->getMessage(), [
                'row_data' => $row,
                'mapped_data' => $mappedData ?? []
            ]);
            return null;
        }
    }

    protected function mapRowData(array $row): array
    {
        // Si aucun mapping spécifié, tenter un mapping par index identique
        if (empty($this->mapping)) {
            return $row;
        }

        $mappedData = [];
        foreach ($this->mapping as $field => $config) {
            $columnIndex = $config['column_index'] ?? null;
            if ($columnIndex !== null && isset($row[$columnIndex])) {
                $value = is_string($row[$columnIndex]) ? trim($row[$columnIndex]) : $row[$columnIndex];
                if ($value !== '' && $value !== null) {
                    $mappedData[$field] = $value;
                }
            }
        }
        return $mappedData;
    }

    protected function validateRequiredFields(array $data): bool
    {
        $requiredFields = ['code', 'name', 'level', 'status', 'support', 'activity'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    protected function createOrUpdateRecord(array $data): ?Record
    {
        $recordData = [
            'code' => $data['code'],
            'name' => $data['name'],
            'date_format' => $data['date_format'] ?? 'Y',
            'date_start' => $this->formatDate($data['start_date'] ?? null),
            'date_end' => $this->formatDate($data['end_date'] ?? null),
            'date_exact' => $this->formatDate($data['exact_date'] ?? null),
            'level_id' => $this->getOrCreateLevel($data['level'])->id,
            'width' => is_numeric($data['width'] ?? null) ? $data['width'] : null,
            'width_description' => $data['width_description'] ?? null,
            'biographical_history' => $data['biographical_history'] ?? null,
            'archival_history' => $data['archival_history'] ?? null,
            'acquisition_source' => $data['acquisition_source'] ?? null,
            'content' => $data['content'] ?? null,
            'appraisal' => $data['appraisal'] ?? null,
            'accrual' => $data['accrual'] ?? null,
            'arrangement' => $data['arrangement'] ?? null,
            'access_conditions' => $data['access_conditions'] ?? null,
            'reproduction_conditions' => $data['reproduction_conditions'] ?? null,
            'language_material' => $data['language_material'] ?? null,
            'characteristic' => $data['characteristic'] ?? null,
            'finding_aids' => $data['finding_aids'] ?? null,
            'location_original' => $data['location_original'] ?? null,
            'location_copy' => $data['location_copy'] ?? null,
            'related_unit' => $data['related_unit'] ?? null,
            'publication_note' => $data['publication_note'] ?? null,
            'note' => $data['note'] ?? null,
            'archivist_note' => $data['archivist_note'] ?? null,
            'rule_convention' => $data['rule_convention'] ?? null,
            'status_id' => $this->getOrCreateStatus($data['status'])->id,
            'support_id' => $this->getOrCreateSupport($data['support'])->id,
            'activity_id' => $this->getOrCreateActivity($data['activity'])->id,
            'user_id' => Auth::id() ?? 1,
            'organisation_id' => optional(Auth::user())->current_organisation_id ?? 1,
        ];

        if ($this->updateExisting) {
            $existing = Record::where('code', $data['code'])->first();
            if ($existing) {
                $existing->update($recordData);
                return $existing;
            }
        }

        return Record::create($recordData);
    }

    protected function processAuthors(Record $record, array $data): void
    {
        if (empty($data['authors'])) {
            return;
        }
        $authors = array_map('trim', explode(',', (string) $data['authors']));
        $ids = [];
        foreach ($authors as $name) {
            if ($name === '') { continue; }
            $author = Author::firstOrCreate(['name' => $name]);
            $ids[] = $author->id;
        }
        if (!empty($ids)) {
            $record->authors()->sync($ids);
        }
    }

    protected function processTerms(Record $record, array $data): void
    {
        if (empty($data['terms'])) {
            return;
        }
        $terms = array_map('trim', explode(',', (string) $data['terms']));
        $attach = [];
        foreach ($terms as $termName) {
            if ($termName === '') { continue; }
            $concept = ThesaurusConcept::whereHas('labels', function ($q) use ($termName) {
                $q->where('literal_form', 'LIKE', '%' . $termName . '%');
            })->first();
            if ($concept) {
                $attach[$concept->id] = [
                    'weight' => 1.0,
                    'context' => 'import',
                    'extraction_note' => 'Importé depuis fichier',
                ];
            }
        }
        if (!empty($attach)) {
            $record->thesaurusConcepts()->sync($attach);
        }
    }

    protected function formatDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd-m-Y'];
        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $date);
            if ($dt && $dt->format($format) === $date) {
                return $dt->format('Y-m-d');
            }
        }
        return null;
    }

    protected function getOrCreateLevel(string $name): RecordLevel
    {
        return RecordLevel::firstOrCreate(['name' => $name], ['name' => $name]);
    }

    protected function getOrCreateStatus(string $name): RecordStatus
    {
        return RecordStatus::firstOrCreate(['name' => $name], ['name' => $name]);
    }

    protected function getOrCreateSupport(string $name): RecordSupport
    {
        return RecordSupport::firstOrCreate(['name' => $name], ['name' => $name]);
    }

    protected function getOrCreateActivity(string $name): Activity
    {
        return Activity::firstOrCreate(['name' => $name], ['name' => $name]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}
