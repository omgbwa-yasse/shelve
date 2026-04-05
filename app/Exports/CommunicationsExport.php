<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;


class CommunicationsExport implements WithMultipleSheets
{
    use Exportable;

    protected $communications;

    public function __construct($communications)
    {
        $this->communications = $communications;
    }

    public function sheets(): array
    {
        $sheets = [
            new CommunicationsSheet($this->communications)
        ];

        // Ajout d'un onglet pour les records
        $sheets[] = new RecordsSheet($this->communications);

        return $sheets;
    }
}

class CommunicationsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $communications;


    public function __construct($communications)
    {
        $this->communications = $communications;
    }

    public function title(): string
    {
        return 'Bordereau';
    }

    public function collection()
    {
        return $this->communications->map(function ($communication) {
            return [
                'Code' => $communication->code,
                'Name' => $communication->name,
                'Content' => $communication->content,
                'User' => $communication->user->name ?? 'N/A',
                'User Organisation' => $communication->userOrganisation->name ?? 'N/A',
                'Operator' => $communication->operator->name ?? 'N/A',
                'Operator Organisation' => $communication->operatorOrganisation->name ?? 'N/A',
                'Return Date' => $communication->return_date ?? 'N/A',
                'Return Effective' => $communication->return_effective ?? 'N/A',
                'Status' => $communication->status->label() ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Content',
            'User',
            'User Organisation',
            'Operator',
            'Operator Organisation',
            'Return Date',
            'Return Effective',
            'Status',
        ];
    }
}

class RecordsSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $communications;



    public function __construct($communications)
    {
        $this->communications = $communications;
    }


    public function title(): string
    {
        return 'Documents';
    }

    public function collection()
    {
        $records = collect();

        foreach ($this->communications as $communication) {
            foreach ($communication->records as $record) {
                $records->push([
                    'Communication Code' => $communication->code,
                    'Communication Name' => $communication->name,
                    'Record Code' => $record->code ?? 'N/A',
                    'Record Name' => $record->name ?? 'N/A',
                    'Date Format' => $record->date_format ?? 'N/A',
                    'Date Start' => $record->date_start ?? 'N/A',
                    'Date End' => $record->date_end ?? 'N/A',
                    'Date Exact' => $record->date_exact ?? 'N/A',
                    'Level' => $record->level_id ?? 'N/A',
                    'Width' => $record->width ?? 'N/A',
                    'Width Description' => $record->width_description ?? 'N/A',
                    'Biographical History' => $record->biographical_history ?? 'N/A',
                    'Archival History' => $record->archival_history ?? 'N/A',
                    'Acquisition Source' => $record->acquisition_source ?? 'N/A',
                    'Content' => $record->pivot->content ?? 'N/A',
                    'Is Original' => $record->pivot->is_original ? 'Yes' : 'No',
                    'Return Date' => $record->pivot->return_date ?? 'N/A',
                    'Return Effective' => $record->pivot->return_effective ?? 'N/A'
                ]);
            }
        }

        return $records;
    }

    public function headings(): array
    {
        return [
            'Communication Code',
            'Communication Name',
            'Record Code',
            'Record Name',
            'Date Format',
            'Date Start',
            'Date End',
            'Date Exact',
            'Level',
            'Width',
            'Width Description',
            'Biographical History',
            'Archival History',
            'Acquisition Source',
            'Content',
            'Is Original',
            'Return Date',
            'Return Effective'
        ];
    }
}
