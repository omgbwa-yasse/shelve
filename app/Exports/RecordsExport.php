<?php

namespace App\Exports;

use App\Models\Record;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RecordsExport  implements FromCollection
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records;
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
