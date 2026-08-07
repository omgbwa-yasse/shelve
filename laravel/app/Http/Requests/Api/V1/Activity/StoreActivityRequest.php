<?php

namespace App\Http\Requests\Api\V1\Activity;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une activité — D01.
 *
 * Règles reprises de `ActivityController::store()` (relu le 2026-08-04).
 */
class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|unique:activities|max:10',  // reprise du contrôleur Blade
            'name' => 'required|max:100',  // reprise du contrôleur Blade
            'observation' => 'nullable',  // reprise du contrôleur Blade
            'parent_id' => 'nullable|exists:activities,id',  // reprise du contrôleur Blade
            'communicability_id' => ['nullable', 'integer', 'exists:communicabilities,id'],
        ];
    }
}
