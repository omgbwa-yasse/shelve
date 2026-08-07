<?php

namespace App\Http\Requests\Api\V1\WorkplaceTemplate;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un modèle d'espace de travail — D12.
 *
 * Règles reprises de `WorkplaceTemplateController::store()` (relu le 2026-08-04).
 * `code`, `is_active`, `is_system`, `created_by` posés côté serveur.
 */
class StoreWorkplaceTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'category' => 'nullable|string',
            'default_structure' => 'nullable|json',
            'default_settings' => 'nullable|json',
        ];
    }
}
