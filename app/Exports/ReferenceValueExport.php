<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export .xlsx des valeurs d'un domaine (étape 7). Colonnes : code, valeur,
 * description, actif, ordre, propriétés supplémentaires.
 */
class ReferenceValueExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        private $list,
    ) {}

    public function collection(): Collection
    {
        return $this->list->values()
            ->withTrashed()
            ->orderBy('sort_order')
            ->orderBy('value')
            ->get();
    }

    public function headings(): array
    {
        return ['code', 'value', 'description', 'active', 'sort_order', 'extra_attributes'];
    }

    public function map($value): array
    {
        return [
            $value->code,
            $value->value,
            $value->description,
            $value->active ? 'oui' : 'non',
            $value->sort_order,
            $value->extra_attributes ? json_encode($value->extra_attributes, JSON_UNESCAPED_UNICODE) : null,
        ];
    }
}
