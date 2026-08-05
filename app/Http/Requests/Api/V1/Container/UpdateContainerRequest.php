<?php

namespace App\Http\Requests\Api\V1\Container;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un conteneur — D03.
 *
 * Règles reprises de `ContainerController::update()` (relu le 2026-08-04).
 * `creator_id` et `creator_organisation_id` sont posés depuis l'agent authentifié,
 * jamais acceptés du client.
 */
class UpdateContainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes', 'required', 'string', 'max:20',
                Rule::unique('containers', 'code')->ignore($this->route('container')),
            ],
            'shelve_id' => 'sometimes|required|exists:shelves,id',
            'status_id' => 'sometimes|required|exists:container_statuses,id',
            'property_id' => 'sometimes|required|exists:container_properties,id',
            'is_archived' => 'boolean',
        ];
    }
}
