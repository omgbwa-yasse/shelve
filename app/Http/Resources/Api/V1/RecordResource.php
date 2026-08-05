<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D02 — notice, relue le 2026-08-04 contre `RecordController` et le schéma
 * (modèle unifié `Record` : `RecordPhysical`/`RecordDigital*` fusionnés).
 *
 * Champs calculés repris de l'arbre Blade : `is_container`, `is_root`. `deleted_at`
 * n'est pas exposé (suppression logique interne).
 */
class RecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'biographical_history' => $this->biographical_history,
            'archival_history' => $this->archival_history,
            'acquisition_source' => $this->acquisition_source,
            'content' => $this->content,
            'appraisal' => $this->appraisal,
            'accrual' => $this->accrual,
            'arrangement' => $this->arrangement,
            'access_conditions' => $this->access_conditions,
            'reproduction_conditions' => $this->reproduction_conditions,
            'language_material' => $this->language_material,
            'characteristic' => $this->characteristic,
            'finding_aids' => $this->finding_aids,
            'location_original' => $this->location_original,
            'location_copy' => $this->location_copy,
            'related_unit' => $this->related_unit,
            'publication_note' => $this->publication_note,
            'note' => $this->note,
            'archivist_note' => $this->archivist_note,
            'rule_convention' => $this->rule_convention,
            'extent' => $this->extent,
            'category_precision' => $this->category_precision,
            'opening_date' => $this->opening_date ? Carbon::parse($this->opening_date)->toDateString() : null,
            'closing_date' => $this->closing_date ? Carbon::parse($this->closing_date)->toDateString() : null,
            'old_record_number' => $this->old_record_number,
            'unavailable' => (bool) $this->unavailable,
            'annual_opening' => (bool) $this->annual_opening,
            'is_essential' => (bool) $this->is_essential,
            'loaned_to' => $this->loaned_to,
            'loaned_at' => $this->loaned_at?->toIso8601ZuluString(),
            'loan_planned_return_at' => $this->loan_planned_return_at?->toIso8601ZuluString(),
            'loan_actual_return_at' => $this->loan_actual_return_at?->toIso8601ZuluString(),
            'modified_after_loan' => (bool) $this->modified_after_loan,
            'confidentiality_id' => $this->confidentiality_id,
            'access_limit_id' => $this->access_limit_id,
            'table_of_contents' => $this->table_of_contents,
            'quantity' => $this->quantity,
            'dimension' => $this->dimension,
            'publisher' => $this->publisher,
            'publication_date' => $this->publication_date ? Carbon::parse($this->publication_date)->toDateString() : null,
            'location_before_add' => $this->location_before_add,
            'sort_value' => $this->sort_value,
            'geographic_scope' => $this->geographic_scope,
            'type_id' => $this->type_id,
            'level_id' => $this->level_id,
            'status_id' => $this->status_id,
            'activity_id' => $this->activity_id,
            'parent_id' => $this->parent_id,
            'organisation_id' => $this->organisation_id,
            'creator_id' => $this->creator_id,
            'assigned_to' => $this->assigned_to,
            'access_level' => $this->access_level,
            'requires_approval' => (bool) $this->requires_approval,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toIso8601ZuluString(),
            'metadata' => $this->metadata,
            'start_date' => $this->start_date ? Carbon::parse($this->start_date)->toDateString() : null,
            'end_date' => $this->end_date ? Carbon::parse($this->end_date)->toDateString() : null,
            'date_exact' => $this->date_exact ? Carbon::parse($this->date_exact)->toDateString() : null,
            'date_format' => $this->date_format,
            'version_number' => $this->version_number,
            'is_current_version' => (bool) $this->is_current_version,
            'is_container' => $this->isContainer(),
            'is_root' => $this->isRoot(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
