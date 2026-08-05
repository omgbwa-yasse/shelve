<?php

namespace App\Http\Requests\Api\V1\WorkplaceActivity;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une activité d'espace de travail — D12.
 *
 * Ressource en lecture seule côté API (pas de route de création) : règles
 * conservées pour un usage futur, déduites de la table `workplace_activities`.
 */
class StoreWorkplaceActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workplace_id' => 'required|integer|exists:workplaces,id',
            'user_id' => 'required|integer|exists:users,id',
            'activity_type' => 'required|string',
            'subject_type' => 'nullable|string|max:191',
            'subject_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'metadata' => 'nullable|array',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string|max:191',
        ];
    }
}
