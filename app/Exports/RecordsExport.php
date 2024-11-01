<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class RecordsExport implements FromCollection, WithHeadings, WithMapping
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
            'Code',
            'Nom',
            'Format de date',
            'Date début',
            'Date fin',
            'Date exacte',
            'Niveau',
            'Largeur',
            'Description de la largeur',
            'Histoire biographique',
            'Histoire archivistique',
            'Source d\'acquisition',
            'Contenu',
            'Évaluation',
            'Accroissements',
            'Classement',
            'Conditions d\'accès',
            'Conditions de reproduction',
            'Langue des documents',
            'Caractéristiques matérielles',
            'Instruments de recherche',
            'Localisation des originaux',
            'Localisation des copies',
            'Unités de description associées',
            'Note de publication',
            'Notes',
            'Notes de l\'archiviste',
            'Règles ou conventions',
            'Statut',
            'Support',
            'Activité',
            'Parent',
            'Conteneur',
            'Producteurs',
            'Termes',
            'Organisation',
            'Utilisateur'
        ];
    }

    public function map($record): array
    {
        return [
            $record->code,
            $record->name,
            $record->date_format,
            $record->date_start,
            $record->date_end,
            $record->date_exact,
            $record->level->name ?? 'N/A',
            $record->width,
            $record->width_description,
            $record->biographical_history,
            $record->archival_history,
            $record->acquisition_source,
            $record->content,
            $record->appraisal,
            $record->accrual,
            $record->arrangement,
            $record->access_conditions,
            $record->reproduction_conditions,
            $record->language_material,
            $record->characteristic,
            $record->finding_aids,
            $record->location_original,
            $record->location_copy,
            $record->related_unit,
            $record->publication_note,
            $record->note,
            $record->archivist_note,
            $record->rule_convention,
            $record->status->name ?? 'N/A',
            $record->support->name ?? 'N/A',
            $record->activity->name ?? 'N/A',
            $record->parent->name ?? 'N/A',
            $record->containers->pluck('name')->join('; '),
            $record->authors->pluck('name')->join('; '),
            $record->terms->pluck('name')->join('; '),
            $record->organisation->pluck('name')->join('; '),
            $record->user->name ?? 'N/A'
        ];
    }
}
