<?php

namespace App\Exports;

use App\Models\Record;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RecordsExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return Record::query()->with(['level', 'status', 'support', 'activity', 'parent', 'container', 'user', 'authors', 'terms']);
    }

    public function map($record): array
    {
        return [
            'id' => $record->id,
            'code' => $record->code,
            'name' => $record->name,
            'date_start' => $record->date_start,
            'date_end' => $record->date_end,
            'level' => $record->level->name ?? '',
            'content' => $record->content,
            'status' => $record->status->name ?? '',
            'support' => $record->support->name ?? '',
            'activity' => $record->activity->name ?? '',
            'location_original' => $record->location_original,
            'authors' => $record->authors->pluck('name')->implode(', '),
            // Add more fields as needed
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Name',
            'Start Date',
            'End Date',
            'Level',
            'Content',
            'Status',
            'Support',
            'Activity',
            'Original Location',
            'Authors',
            // Add more headings as needed
        ];
    }
}
