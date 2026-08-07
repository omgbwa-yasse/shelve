<?php

namespace App\Http\Requests\Api\V1\ContainerProperty;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un type de conteneur — D03.
 *
 * Règles reprises de `ContainerPropertyController::update()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class UpdateContainerPropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('container_properties', 'name')->ignore($this->route('container_property')),
            ],
            'width' => 'sometimes|required|numeric',
            'length' => 'sometimes|required|numeric',
            'depth' => 'sometimes|required|numeric',
        ];
    }
}
