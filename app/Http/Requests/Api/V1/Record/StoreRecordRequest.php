<?php

namespace App\Http\Requests\Api\V1\Record;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une notice — D02.
 *
 * Règles reprises de `RecordController::validateRecord()` (relu le 2026-08-04).
 * `organisation_id`, `creator_id`, `version_number` et `is_current_version` sont
 * posés depuis l'agent authentifié dans le contrôleur, jamais acceptés du client.
 * `level_id`/`status_id`/`access_level` retombent sur les valeurs par défaut en
 * l'absence de valeur (comme en Blade).
 */
class StoreRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'code' => 'nullable|string|max:30',
            'description' => 'nullable|string',
            'type_id' => 'nullable|exists:record_types,id',
            'level_id' => 'nullable|exists:record_levels,id',
            'status_id' => 'nullable|exists:record_statuses,id',
            'activity_id' => 'nullable|exists:activities,id',
            'parent_id' => 'nullable|exists:records,id',
            'assigned_to' => 'nullable|exists:users,id',
            'access_level' => 'nullable|string|max:20',
            'requires_approval' => 'nullable|boolean',
            'confidentiality_id' => 'nullable|exists:record_confidentialities,id',
            'access_limit_id' => 'nullable|exists:reference_lists,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'date_exact' => 'nullable|date',
            'date_format' => 'nullable|string|max:1',
            'opening_date' => 'nullable|date',
            'closing_date' => 'nullable|date',
            'extent' => 'nullable|string',
            'table_of_contents' => 'nullable|string',
            'quantity' => 'nullable|string',
            'dimension' => 'nullable|string',
            'publisher' => 'nullable|string',
            'sort_value' => 'nullable|string',
            'geographic_scope' => 'nullable|array',
            'metadata' => 'nullable|array',
            // ISAD(G)
            'biographical_history' => 'nullable|string',
            'archival_history' => 'nullable|string',
            'acquisition_source' => 'nullable|string',
            'content' => 'nullable|string',
            'appraisal' => 'nullable|string',
            'accrual' => 'nullable|string',
            'arrangement' => 'nullable|string',
            'access_conditions' => 'nullable|string',
            'reproduction_conditions' => 'nullable|string',
            'language_material' => 'nullable|string',
            'characteristic' => 'nullable|string',
            'finding_aids' => 'nullable|string',
            'location_original' => 'nullable|string',
            'location_copy' => 'nullable|string',
            'related_unit' => 'nullable|string',
            'publication_note' => 'nullable|string',
            'note' => 'nullable|string',
            'archivist_note' => 'nullable|string',
            'rule_convention' => 'nullable|string',
            // Prêt notice
            'loaned_to' => 'nullable|exists:users,id',
            'loaned_at' => 'nullable|date',
            'loan_planned_return_at' => 'nullable|date',
            'loan_actual_return_at' => 'nullable|date',
            'modified_after_loan' => 'nullable|boolean',
        ];
    }
}
