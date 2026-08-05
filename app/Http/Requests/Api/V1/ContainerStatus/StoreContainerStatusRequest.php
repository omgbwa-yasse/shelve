<?php

namespace App\Http\Requests\Api\V1\ContainerStatus;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un statut de conteneur — D03.
 *
 * Règles reprises de `ContainerStatusController::store()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class StoreContainerStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:50|unique:container_statuses,name',
            'description' => 'nullable',
        ];
    }
}
