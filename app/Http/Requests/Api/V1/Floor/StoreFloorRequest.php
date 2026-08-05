<?php

namespace App\Http\Requests\Api\V1\Floor;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un étage — D03.
 *
 * Règles reprises de `FloorController::store()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class StoreFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:100',
            'description' => 'nullable',
            'building_id' => 'required|exists:buildings,id',
        ];
    }
}
