<?php

namespace App\Http\Requests\Api\V1\Language;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création (Language) — D01, relu le 2026-08-04 contre le contrôleur Blade.
 */
class StoreLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:2|unique:languages',  // reprise du contrôleur Blade
            'name' => 'required|string|max:50',  // reprise du contrôleur Blade
            'native_name' => ['nullable', 'string', 'max:50'],  // déduit du schéma
            'description' => ['nullable', 'string'],  // déduit du schéma
        ];
    }
}
