<?php

namespace App\Http\Requests\Api\V1\Sort;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création (Sort) — D01, relu le 2026-08-04 contre le contrôleur Blade.
 */
class StoreSortRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|unique:sorts|in:E,T,C',  // reprise du contrôleur Blade
            'name' => 'required|max:45',  // reprise du contrôleur Blade
            'description' => 'nullable|max:100',  // reprise du contrôleur Blade
        ];
    }
}
