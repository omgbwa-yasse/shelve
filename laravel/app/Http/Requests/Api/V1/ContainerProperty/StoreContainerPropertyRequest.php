<?php

namespace App\Http\Requests\Api\V1\ContainerProperty;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un type de conteneur — D03.
 *
 * Règles reprises de `ContainerPropertyController::store()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class StoreContainerPropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|unique:container_properties|max:100',
            'width' => 'required|numeric',
            'length' => 'required|numeric',
            'depth' => 'required|numeric',
        ];
    }
}
