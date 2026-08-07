<?php

namespace App\Http\Requests\Api\V1\Floor;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un étage — D03.
 *
 * Règles reprises de `FloorController::update()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class UpdateFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|max:100',
            'description' => 'nullable',
            'building_id' => 'sometimes|required|exists:buildings,id',
        ];
    }
}
