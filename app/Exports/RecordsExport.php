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
            // 'Nom', // Déplacé plus bas dans l'ordre demandé
            // 'Format de date', // Non requis
            // 'Date début', // Sera combiné dans Dates
            // 'Date fin', // Sera combiné dans Dates
            // 'Date exacte', // Sera combiné dans Dates
            'Niveau', // level
            'Support',
            'Nom', // name
            'Dates', // Combinaison des dates
            'Largeur', // width
            // 'Description de la largeur', // Non requis
            // 'Histoire biographique', // Non requis
            // 'Histoire archivistique', // Non requis
            // 'Source d\'acquisition', // Non requis
            'Contenu', // content
            // 'Évaluation', // Non requis
            // 'Accroissements', // Non requis
            // 'Classement', // Non requis
            // 'Conditions d\'accès', // Non requis
            // 'Conditions de reproduction', // Non requis
            // 'Langue des documents', // Non requis
            // 'Caractéristiques matérielles', // Non requis
            // 'Instruments de recherche', // Non requis
            // 'Localisation des originaux', // Non requis
            // 'Localisation des copies', // Non requis
            // 'Unités de description associées', // Non requis
            // 'Note de publication', // Non requis
            // 'Notes', // Non requis
            // 'Notes de l\'archiviste', // Non requis
            // 'Règles ou conventions', // Non requis
            'Statut', // status
            // 'Support', // Déjà inclus plus haut
            'Activité', // classes/activity
            // 'Parent', // Non requis
            'Conteneur', // location/containers
            'Producteurs', // authors
            'Termes', // terms
            'Mots-clés', // keywords
            // 'Organisation', // Non requis
            // 'Utilisateur' // Non requis
        ];
    }
    private function safeRelationPluck($relation, $key = 'name'): string
    {
        if (!$relation || !$relation->count()) {
            return 'N/A';
        }

        // Si c'est une relation thesaurusConcepts, utiliser preferred_label
        if ($relation->first() instanceof \App\Models\ThesaurusConcept) {
            $key = 'preferred_label';
        }

        return $relation->pluck($key)->filter()->join('; ') ?: 'N/A';
    }
    public function map($record): array
    {
        return [
            $record->code ?? 'N/A',
            $record->level->name ?? 'N/A',
            $record->support->name ?? 'N/A',
            $record->name ?? 'N/A',
            // Formatage des dates avec tiret (-) et gestion des nulls
            ($record->date_start && $record->date_end)
                ? "{$record->date_start} - {$record->date_end}"
                : ($record->date_exact ?? 'N/A'),
            $record->width ?? 'N/A',
            $record->content ?? 'N/A',
            $record->status->name ?? 'N/A',
            $record->activity->name ?? 'N/A', // Activité (classes)

            $this->safeRelationPluck($record->containers),
            $this->safeRelationPluck($record->authors), // Producteurs (auteurs) sécurisé
            $this->safeRelationPluck($record->thesaurusConcepts), // Concepts du thésaurus sécurisé


            $this->safeRelationPluck($record->keywords),
            // $record->name, // Non requis - Déplacé plus haut
            // $record->date_format, // Non requis
            // $record->date_start, // Inclus dans le champ Dates
            // $record->date_end, // Inclus dans le champ Dates
            // $record->date_exact, // Inclus dans le champ Dates
            // $record->width_description, // Non requis
            // $record->biographical_history, // Non requis
            // $record->archival_history, // Non requis
            // $record->acquisition_source, // Non requis
            // $record->appraisal, // Non requis
            // $record->accrual, // Non requis
            // $record->arrangement, // Non requis
            // $record->access_conditions, // Non requis
            // $record->reproduction_conditions, // Non requis
            // $record->language_material, // Non requis
            // $record->characteristic, // Non requis
            // $record->finding_aids, // Non requis
            // $record->location_original, // Non requis
            // $record->location_copy, // Non requis
            // $record->related_unit, // Non requis
            // $record->publication_note, // Non requis
            // $record->note, // Non requis
            // $record->archivist_note, // Non requis
            // $record->rule_convention, // Non requis
            // $record->parent->name ?? 'N/A', // Non requis
            // $record->organisation->pluck('name')->join('; '), // Non requis
            // $record->user->name ?? 'N/A' // Non requis
        ];
    }

}
