<?php

namespace App\Http\Requests\Api\V1\Communicability;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création (Communicability) — D01, relu le 2026-08-04 contre le contrôleur Blade.
 */
class StoreCommunicabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|unique:communicabilities|max:10',  // reprise du contrôleur Blade
            'name' => 'required|max:100',  // reprise du contrôleur Blade
            'duration' => 'required|integer',  // reprise du contrôleur Blade
            'description' => 'nullable',  // reprise du contrôleur Blade
        ];
    }
}
