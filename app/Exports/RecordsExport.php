<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;
use App\Models\RecordPhysical;

class RecordsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $records;

    public function __construct($records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        // Charger toutes les relations nécessaires pour éviter le N+1
        if ($this->records instanceof Collection) {
            return $this->records->load(['level', 'status', 'support', 'activity', 'containers', 'authors', 'thesaurusConcepts.labels', 'organisation', 'parent']);
        }
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'id',
            'code',
            'name',
            'date_format',
            'date_start',
            'date_end',
            'date_exact',
            'level',
            'width',
            'width_description',
            'biographical_history',
            'archival_history',
            'acquisition_source',
            'content',
            'appraisal',
            'accrual',
            'arrangement',
            'access_conditions',
            'reproduction_conditions',
            'language_material',
            'characteristic',
            'finding_aids',
            'location_original',
            'location_copy',
            'related_unit',
            'publication_note',
            'note',
            'archivist_note',
            'rule_convention',
            'status',
            'support',
            'activity',
            'parent',
            'containers',
            'authors',
            'terms',
            'organisation',
            'user_id',
            'created_at',
            'updated_at',
        ];
    }
    private function safeRelationPluck($relation, $key = 'name'): string
    {
        if (!$relation || !$relation->count()) {
            return 'N/A';
        }

        // Spécifique thésaurus: chercher le prefLabel fr-fr si possible
        $first = $relation->first();
        if ($first instanceof \App\Models\ThesaurusConcept) {
            $labels = [];
            foreach ($relation as $concept) {
                $pref = $concept->labels()->where('type', 'prefLabel')->where('language', 'fr-fr')->first();
                $labels[] = $pref?->literal_form ?? $concept->uri;
            }
            return collect($labels)->filter()->join('; ');
        }

        return $relation->pluck($key)->filter()->join('; ') ?: 'N/A';
    }
    public function map($record): array
    {
        return [
            $record->id,
            $record->code,
            $record->name,
            $record->date_format,
            $record->date_start,
            $record->date_end,
            $record->date_exact,
            optional($record->level)->name,
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
            optional($record->status)->name,
            optional($record->support)->name,
            optional($record->activity)->name,
            optional($record->parent)->name,
            $this->safeRelationPluck($record->containers),
            $this->safeRelationPluck($record->authors),
            $this->safeRelationPluck($record->thesaurusConcepts),
            optional($record->organisation)->name,
            $record->user_id,
            $record->created_at,
            $record->updated_at,
        ];
    }

}
