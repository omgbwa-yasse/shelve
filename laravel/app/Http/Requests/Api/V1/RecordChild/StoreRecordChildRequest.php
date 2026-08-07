<?php

namespace App\Http\Requests\Api\V1\RecordChild;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une notice fille — D02.
 *
 * Règles reprises de `RecordChildController::store()` (relu le 2026-08-04), réduites
 * au cœur descriptif : `parent_id`, `organisation_id`, `creator_id` et les colonnes de
 * version sont posés depuis l'agent/la notice parente dans le contrôleur (jamais du client).
 */
class StoreRecordChildRequest extends FormRequest
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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'date_exact' => 'nullable|date',
            'date_format' => 'nullable|string|max:1',
            // Champs descriptifs (content, biographical_history, ...) : désormais des
            // MetadataDefinition rattachées au RecordType, validées dynamiquement dans
            // le contrôleur via MetadataValidationService.
            'metadata' => 'nullable|array',
        ];
    }
}
