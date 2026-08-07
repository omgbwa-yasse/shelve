<?php

namespace App\Http\Requests\Api\V1\WorkplaceActivity;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une activité d'espace de travail — D12.
 *
 * Ressource en lecture seule côté API : règles conservées pour un usage futur.
 */
class UpdateWorkplaceActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workplace_id' => 'sometimes|required|integer|exists:workplaces,id',
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'activity_type' => 'sometimes|required|string',
            'subject_type' => 'nullable|string|max:191',
            'subject_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'metadata' => 'nullable|array',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:191',
        ];
    }
}
