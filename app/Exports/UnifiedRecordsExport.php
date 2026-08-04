<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class UnifiedRecordsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
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
        return [
            'id',
            'code',
            'name',
            'type',
            'level',
            'status',
            'activity',
            'date_exact',
            'date_start',
            'date_end',
            'content',
            'description',
            'archival_history',
            'biographical_history',
            'access_conditions',
            'note',
            'organisation',
            'parent',
            'version',
            'created_at',
        ];
    }

    public function map($record): array
    {
        return [
            $record->id,
            $record->code,
            $record->name,
            $record->type?->name,
            $record->level?->name,
            $record->status?->name,
            $record->activity?->name,
            $record->date_exact?->format('Y-m-d'),
            $record->start_date?->format('Y-m-d'),
            $record->end_date?->format('Y-m-d'),
            $record->content,
            $record->description,
            $record->archival_history,
            $record->biographical_history,
            $record->access_conditions,
            $record->note,
            $record->organisation?->name,
            $record->parent ? ($record->parent->code . ' ' . $record->parent->name) : '',
            $record->version_number,
            $record->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
