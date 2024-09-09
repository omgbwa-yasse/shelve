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
            'date_format' => $record->date_format,
            'date_start' => $record->date_start,
            'date_end' => $record->date_end,
            'date_exact' => $record->date_exact,
            'level' => $record->level->name ?? '',
            'width' => $record->width,
            'width_description' => $record->width_description,
            'biographical_history' => $record->biographical_history,
            'archival_history' => $record->archival_history,
            'acquisition_source' => $record->acquisition_source,
            'content' => $record->content,
            'appraisal' => $record->appraisal,
            'accrual' => $record->accrual,
            'arrangement' => $record->arrangement,
            'access_conditions' => $record->access_conditions,
            'reproduction_conditions' => $record->reproduction_conditions,
            'language_material' => $record->language_material,
            'characteristic' => $record->characteristic,
            'finding_aids' => $record->finding_aids,
            'location_original' => $record->location_original,
            'location_copy' => $record->location_copy,
            'related_unit' => $record->related_unit,
            'publication_note' => $record->publication_note,
            'note' => $record->note,
            'archivist_note' => $record->archivist_note,
            'rule_convention' => $record->rule_convention,
            'status' => $record->status->name ?? '',
            'support' => $record->support->name ?? '',
            'activity' => $record->activity->name ?? '',
            'parent' => $record->parent->name ?? '',
            'container' => $record->container->name ?? '',
            'user' => $record->user->name ?? '',
            'authors' => $record->authors->pluck('name')->implode(', '),
            'terms' => $record->terms->pluck('name')->implode(', '),
            // Add more fields as needed
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Name',
            'Date Format',
            'Start Date',
            'End Date',
            'Exact Date',
            'Level',
            'Width',
            'Width Description',
            'Biographical History',
            'Archival History',
            'Acquisition Source',
            'Content',
            'Appraisal',
            'Accrual',
            'Arrangement',
            'Access Conditions',
            'Reproduction Conditions',
            'Language Material',
            'Characteristic',
            'Finding Aids',
            'Original Location',
            'Copy Location',
            'Related Unit',
            'Publication Note',
            'Note',
            'Archivist Note',
            'Rule Convention',
            'Status',
            'Support',
            'Activity',
            'Parent',
            'Container',
            'User',
            'Authors',
            'Terms',
            // Add more headings as needed
        ];
    }
}
