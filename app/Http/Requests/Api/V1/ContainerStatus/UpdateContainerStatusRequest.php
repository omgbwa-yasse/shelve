<?php

namespace App\Http\Requests\Api\V1\ContainerStatus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un statut de conteneur — D03.
 *
 * Règles reprises de `ContainerStatusController::update()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class UpdateContainerStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('container_statuses', 'name')->ignore($this->route('container_status')),
            ],
            'description' => 'nullable',
        ];
    }
}
