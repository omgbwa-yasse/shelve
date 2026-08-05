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
            // Les anciens champs descriptifs figés sont désormais des MetadataDefinition
            // rattachées au RecordType — validées dynamiquement dans le contrôleur.
            'metadata' => 'nullable|array',
            // Prêt notice
            'loaned_to' => 'sometimes|nullable|exists:users,id',
            'loaned_at' => 'sometimes|nullable|date',
            'loan_planned_return_at' => 'sometimes|nullable|date',
            'loan_actual_return_at' => 'sometimes|nullable|date',
            'modified_after_loan' => 'sometimes|nullable|boolean',
        ];
    }
}
