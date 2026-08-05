<?php

namespace App\Services;

use App\Models\MetadataDefinition;
use App\Models\Record;
use App\Models\ReferenceValue;

/**
 * Vérification d'usage d'une ReferenceValue avant suppression (étape 1).
 *
 * Une valeur est « utilisée » dès lors qu'au moins une notice référence son code
 * dans `records.metadata` (JSON), pour une définition liée à la même liste.
 */
class ReferenceValueUsageService
{
    /**
     * Une valeur est-elle référencée par au moins une notice ?
     */
    public function isValueUsed(ReferenceValue $value): bool
    {
        return $this->usageCount($value) > 0;
    }

    /**
     * Nombre de notices référençant la valeur.
     */
    public function usageCount(ReferenceValue $value): int
    {
        $definitions = MetadataDefinition::query()
            ->where('reference_list_id', $value->list_id)
            ->get();

        $count = 0;

        foreach ($definitions as $definition) {
            $column = 'metadata->'.$definition->code;

            $count += Record::query()
                ->where($column, $value->code)
                ->count();
        }

        return $count;
    }

    /**
     * Supprime en masse les valeurs inactives non utilisées. Retourne le nombre
     * de valeurs réellement supprimées.
     */
    public function deleteUnusedInactiveValues($list): int
    {
        $deleted = 0;

        foreach ($list->values()->where('active', false)->get() as $value) {
            if (! $this->isValueUsed($value)) {
                $value->delete();
                $deleted++;
            }
        }

        return $deleted;
    }
}
