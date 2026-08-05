<?php

namespace App\Http\Requests\Api\V1\Building;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un bâtiment — D03.
 *
 * Règles reprises de `BuildingController::store()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client
 * (le contrôleur Blade utilisait un `creator_id => 1` codé en dur — corrigé ici).
 */
class StoreBuildingRequest extends FormRequest
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
            'visibility' => 'required|in:public,private,inherit',
        ];
    }
}
