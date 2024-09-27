<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CommunicationsExport implements FromCollection, WithHeadings
{
    protected $communications;

    public function __construct($communications)
    {
        $this->communications = $communications;
    }

    public function collection()
    {
        return $this->communications->map(function ($communication) {
            return [
                'Code' => $communication->code,
                'Name' => $communication->name,
                'Content' => $communication->content,
                'User' => $communication->user->name??'N/A',
                'User Organisation' => $communication->userOrganisation->name??'N/A',
                'Operator' => $communication->operator->name??'N/A',
                'Operator Organisation' => $communication->operatorOrganisation->name??'N/A',
                'Return Date' => $communication->return_date??'N/A',
                'Return Effective' => $communication->return_effective??'N/A',
                'Status' => $communication->status->name??'N/A',
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
