<?php

namespace App\Exports;

use App\Models\Slip;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SlipExport implements WithMultipleSheets
{
    protected $slip;

    public function __construct(Slip $slip)
    {
        $this->slip = $slip;
    }

    public function sheets(): array
    {
        return [
            new SlipSummarySheet($this->slip),
            new SlipRecordsSheet($this->slip),
        ];
    }
}

class SlipSummarySheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $slip;
    private const DATE_FORMAT = 'd/m/Y';
    private const DATETIME_FORMAT = 'd/m/Y H:i';

    public function __construct(Slip $slip)
    {
        $this->slip = $slip;
    }

    public function collection()
    {
        // Retourner une collection avec les données du slip
        return collect([$this->slip]);
    }

    public function title(): string
    {
        return 'Sommaire';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code du bordereau',
            'Titre',
            'Description',
            'Service versant',
            'Responsable versement',
            'Service d\'archives',
            'Responsable archives',
            'Statut',
            'Date de création',
            'Date de réception',
            'Date d\'approbation',
            'Reçu',
            'Approuvé',
            'Intégré',
            'Nombre de documents',
        ];
    }

    public function map($slip): array
    {
        return [
            $slip->id,
            $slip->code,
            $slip->name,
            $slip->description ?? '',
            $slip->userOrganisation->name ?? '',
            $slip->user->name ?? '',
            $slip->officerOrganisation->name ?? '',
            $slip->officer->name ?? '',
            $slip->slipStatus->name ?? 'Sans statut',
            $slip->created_at->format(self::DATETIME_FORMAT),
            $slip->received_date ? \Carbon\Carbon::parse($slip->received_date)->format(self::DATE_FORMAT) : '',
            $slip->approved_date ? \Carbon\Carbon::parse($slip->approved_date)->format(self::DATE_FORMAT) : '',
            $slip->is_received ? 'Oui' : 'Non',
            $slip->is_approved ? 'Oui' : 'Non',
            $slip->is_integrated ? 'Oui' : 'Non',
            $slip->records->count(),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}

class SlipRecordsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $slip;
    private const DATE_FORMAT = 'd/m/Y';
    private const DATETIME_FORMAT = 'd/m/Y H:i';

    public function __construct(Slip $slip)
    {
        $this->slip = $slip;
    }

    public function collection()
    {
        return $this->slip->records;
    }

    public function title(): string
    {
        return 'Liste des versements';
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cote',
            'Titre',
            'Description',
            'Auteur',
            'Date exacte',
            'Date de début',
            'Date de fin',
            'Date formatée',
            'Niveau',
            'Support',
            'Activité',
            'Codes containers',
            'Observation',
            'Date de création',
            'Format de date',
            'Largeur',
            'Description largeur',
            'Histoire biographique',
            'Histoire archivistique',
            'Source d\'acquisition',
            'Évaluation',
            'Accroissement',
            'Classement',
            'Conditions d\'accès',
            'Conditions de reproduction',
            'Langue du matériel',
            'Caractéristique',
            'Instruments de recherche',
            'Emplacement original',
            'Emplacement de copie',
            'Unité liée',
            'Note de publication',
            'Note',
            'Note d\'archiviste',
            'Convention de règles',
            'Parent',
            'Utilisateur',
            'Auteurs',
            'Termes',
        ];
    }

    public function map($record): array
    {
        // Formatage de la date
        $dateFormatted = '';
        if ($record->date_exact) {
            $dateFormatted = \Carbon\Carbon::parse($record->date_exact)->format(self::DATE_FORMAT);
        } elseif ($record->date_start && $record->date_end) {
            $dateFormatted = \Carbon\Carbon::parse($record->date_start)->format(self::DATE_FORMAT) . ' - ' . \Carbon\Carbon::parse($record->date_end)->format(self::DATE_FORMAT);
        } elseif ($record->date_start) {
            $dateFormatted = 'Depuis ' . \Carbon\Carbon::parse($record->date_start)->format(self::DATE_FORMAT);
        }

        // Codes des containers
        $containerCodes = '';
        if ($record->containers && $record->containers->isNotEmpty()) {
            $containerCodes = $record->containers->pluck('code')->join(', ');
        }

        return [
            $record->id,
            $record->code,
            $record->name,
            $record->content ?? '',
            $record->author->name ?? '',
            $record->date_exact ? \Carbon\Carbon::parse($record->date_exact)->format(self::DATE_FORMAT) : '',
            $record->date_start ? \Carbon\Carbon::parse($record->date_start)->format(self::DATE_FORMAT) : '',
            $record->date_end ? \Carbon\Carbon::parse($record->date_end)->format(self::DATE_FORMAT) : '',
            $dateFormatted,
            $record->level->name ?? '',
            $record->support->name ?? '',
            $record->activity->name ?? '',
            $containerCodes,
            $record->content ?? '',
            $record->created_at->format(self::DATETIME_FORMAT),
            $record->date_format ?? '',
            $record->width ?? '',
            $record->width_description ?? '',
            $record->biographical_history ?? '',
            $record->archival_history ?? '',
            $record->acquisition_source ?? '',
            $record->appraisal ?? '',
            $record->accrual ?? '',
            $record->arrangement ?? '',
            $record->access_conditions ?? '',
            $record->reproduction_conditions ?? '',
            $record->language_material ?? '',
            $record->characteristic ?? '',
            $record->finding_aids ?? '',
            $record->original_location ?? '',
            $record->copy_location ?? '',
            $record->related_unit ?? '',
            $record->publication_note ?? '',
            $record->note ?? '',
            $record->archivist_note ?? '',
            $record->rule_convention ?? '',
            $record->parent->name ?? '',
            $record->user->name ?? '',
            $record->authors ? $record->authors->pluck('name')->join(', ') : '',
            $record->thesaurusConcepts ? $record->thesaurusConcepts->pluck('preferred_label')->join(', ') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE0E0E0'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
