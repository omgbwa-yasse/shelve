<?php

namespace App\Http\Requests\Api\V1\Container;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un conteneur — D03.
 *
 * Règles reprises de `ContainerController::store()` (relu le 2026-08-04).
 * `creator_id` et `creator_organisation_id` sont posés depuis l'agent authentifié,
 * jamais acceptés du client.
 */
class StoreContainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|max:20|unique:containers,code',
            'shelve_id' => 'required|exists:shelves,id',
            'status_id' => 'required|exists:container_statuses,id',
            'property_id' => 'required|exists:container_properties,id',
            'is_archived' => 'boolean',
        ];
    }
}
