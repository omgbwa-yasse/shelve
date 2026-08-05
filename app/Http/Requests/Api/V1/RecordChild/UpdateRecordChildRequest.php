<?php

namespace App\Http\Requests\Api\V1\RecordChild;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une notice fille — D02.
 *
 * Même socle que StoreRecordChildRequest, en `sometimes` pour la sémantique PATCH.
 */
class UpdateRecordChildRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:191',
            'code' => 'sometimes|nullable|string|max:30',
            'description' => 'nullable|string',
            'type_id' => 'sometimes|nullable|exists:record_types,id',
            'level_id' => 'sometimes|nullable|exists:record_levels,id',
            'status_id' => 'sometimes|nullable|exists:record_statuses,id',
            'activity_id' => 'sometimes|nullable|exists:activities,id',
            'start_date' => 'sometimes|nullable|date',
            'end_date' => 'sometimes|nullable|date',
            'date_exact' => 'sometimes|nullable|date',
            'date_format' => 'sometimes|nullable|string|max:1',
            // Champs descriptifs : désormais des MetadataDefinition, validées
            // dynamiquement dans le contrôleur.
            'metadata' => 'nullable|array',
        ];
    }
}
