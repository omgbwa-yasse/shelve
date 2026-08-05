<?php

namespace App\Http\Requests\Api\V1\Record;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une notice — D02.
 *
 * Règles reprises de `RecordController::validateRecord()` (relu le 2026-08-04),
 * passées en `sometimes` pour la sémantique PATCH. `code` est unique dans `records`,
 * avec `ignore` sur la notice en cours. Champs gérés serveur exclus comme en Store.
 */
class UpdateRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $recordId = $this->route('record');

        return [
            'name' => 'sometimes|required|string|max:191',
            'code' => ['sometimes', 'nullable', 'string', 'max:30', Rule::unique('records', 'code')->ignore($recordId)],
            'description' => 'nullable|string',
            'type_id' => 'sometimes|nullable|exists:record_types,id',
            'level_id' => 'sometimes|nullable|exists:record_levels,id',
            'status_id' => 'sometimes|nullable|exists:record_statuses,id',
            'activity_id' => 'sometimes|nullable|exists:activities,id',
            'parent_id' => 'sometimes|nullable|exists:records,id',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'access_level' => 'sometimes|nullable|string|max:20',
            'requires_approval' => 'sometimes|nullable|boolean',
            'confidentiality_id' => 'sometimes|nullable|exists:record_confidentialities,id',
            'access_limit_id' => 'sometimes|nullable|exists:reference_lists,id',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date',
            'date_exact' => 'sometimes|nullable|date',
            'date_format' => 'sometimes|nullable|string|max:1',
            'opening_date' => 'sometimes|nullable|date',
            'closing_date' => 'sometimes|nullable|date',
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
            'loaned_to' => 'sometimes|nullable|exists:users,id',
            'loaned_at' => 'sometimes|nullable|date',
            'loan_planned_return_at' => 'sometimes|nullable|date',
            'loan_actual_return_at' => 'sometimes|nullable|date',
            'modified_after_loan' => 'sometimes|nullable|boolean',
        ];
    }
}
