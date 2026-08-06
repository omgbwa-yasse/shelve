<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class UnifiedRecordsExport implements FromCollection, WithHeadings, WithMapping
{
    /** Champs exportables (colonnes) avec leur méthode de lecture. */
    public const FIELDS = [
        'id' => null,
        'code' => null,
        'name' => null,
        'type' => 'type',
        'level' => 'level',
        'status' => 'status',
        'activity' => 'activity',
        'date_exact' => null,
        'date_start' => null,
        'date_end' => null,
        'content' => 'meta:content',
        'description' => null,
        'archival_history' => 'meta:archival_history',
        'biographical_history' => 'meta:biographical_history',
        'access_conditions' => 'meta:access_conditions',
        'note' => 'meta:note',
        'organisation' => 'organisation',
        'parent' => 'parent',
        'version' => null,
        'created_at' => null,
    ];

    protected $records;
    protected ?array $fields;

    public function __construct($records, ?array $fields = null)
    {
        $this->records = $records;
        $this->fields = $fields ? array_values(array_intersect($fields, array_keys(self::FIELDS))) : null;
    }

    public function collection()
    {
        if ($this->records instanceof Collection) {
            return $this->records->load(['type', 'level', 'status', 'activity', 'organisation', 'parent', 'authors', 'keywords']);
        }
        return $this->records;
    }

    public function headings(): array
    {
        return $this->fields ?? array_keys(self::FIELDS);
    }

    public function map($record): array
    {
        $fields = $this->fields ?? array_keys(self::FIELDS);

        return array_map(fn ($field) => $this->value($record, $field), $fields);
    }

    protected function value($record, string $field)
    {
        if (str_starts_with($field, 'meta:')) {
            return $record->getMetadataValue(substr($field, 5));
        }

        return match ($field) {
            'id' => $record->id,
            'code' => $record->code,
            'name' => $record->name,
            'type' => $record->type?->name,
            'level' => $record->level?->name,
            'status' => $record->status?->name,
            'activity' => $record->activity?->name,
            'date_exact' => $record->date_exact?->format('Y-m-d'),
            'date_start' => $record->start_date?->format('Y-m-d'),
            'date_end' => $record->end_date?->format('Y-m-d'),
            'description' => $record->description,
            'organisation' => $record->organisation?->name,
            'parent' => $record->parent ? ($record->parent->code . ' ' . $record->parent->name) : '',
            'version' => $record->version_number,
            'created_at' => $record->created_at?->format('Y-m-d H:i:s'),
            default => null,
        };
    }
}
