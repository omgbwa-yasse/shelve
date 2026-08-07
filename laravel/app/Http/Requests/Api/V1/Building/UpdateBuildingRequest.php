<?php

namespace App\Http\Requests\Api\V1\Building;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un bâtiment — D03.
 *
 * Règles reprises de `BuildingController::update()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class UpdateBuildingRequest extends FormRequest
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
            'visibility' => 'sometimes|required|in:public,private,inherit',
        ];
    }
}
