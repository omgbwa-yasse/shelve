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
 * n'est pas exposé (suppression logique interne). Les anciens champs descriptifs
 * figés (content, biographical_history, extent, quantity, ...) sont désormais des
 * MetadataDefinition (système ou personnalisées) exposées uniquement via `metadata`.
 *
 * Les relations ci-dessous ne s'affichent QUE si chargées par le contrôleur
 * (`?include=type,level,...`, voir `RecordController::INCLUDABLE`) — corrige un bug
 * où `with()`/`applyIncludes()` chargeait la relation en mémoire sans jamais la
 * sérialiser (2026-08-05) : la liste/fiche n'affichait que des `*_id` bruts.
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
            'publication_date' => $this->publication_date ? Carbon::parse($this->publication_date)->toDateString() : null,
            'location_before_add' => $this->location_before_add,
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

            'type' => $this->whenLoaded('type', fn () => ['id' => $this->type->id, 'code' => $this->type->code, 'name' => $this->type->name]),
            'level' => $this->whenLoaded('level', fn () => ['id' => $this->level->id, 'name' => $this->level->name]),
            'status' => $this->whenLoaded('status', fn () => ['id' => $this->status->id, 'name' => $this->status->name]),
            'activity' => $this->whenLoaded('activity', fn () => ['id' => $this->activity->id, 'name' => $this->activity->name]),
            'parent' => $this->whenLoaded('parent', fn () => $this->parent ? ['id' => $this->parent->id, 'code' => $this->parent->code, 'name' => $this->parent->name] : null),
            'organisation' => $this->whenLoaded('organisation', fn () => ['id' => $this->organisation->id, 'name' => $this->organisation->name]),
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? ['id' => $this->creator->id, 'name' => $this->creator->name] : null),
            'assignedUser' => $this->whenLoaded('assignedUser', fn () => $this->assignedUser ? ['id' => $this->assignedUser->id, 'name' => $this->assignedUser->name] : null),
            'approver' => $this->whenLoaded('approver', fn () => $this->approver ? ['id' => $this->approver->id, 'name' => $this->approver->name] : null),
            'confidentiality' => $this->whenLoaded('confidentiality', fn () => $this->confidentiality ? ['id' => $this->confidentiality->id, 'name' => $this->confidentiality->name] : null),
            'accessLimit' => $this->whenLoaded('accessLimit', fn () => $this->accessLimit ? ['id' => $this->accessLimit->id, 'name' => $this->accessLimit->name] : null),
        ];
    }
}
