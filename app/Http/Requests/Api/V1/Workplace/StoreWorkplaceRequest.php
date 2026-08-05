<?php

namespace App\Http\Requests\Api\V1\Workplace;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un espace de travail — D12.
 *
 * Règles reprises de `WorkplaceController::store()` (relu le 2026-08-04).
 * `code`, `status`, `organisation_id`, `owner_id`, `created_by` et les compteurs
 * sont posés côté serveur, jamais acceptés du client.
 */
class StoreWorkplaceRequest extends FormRequest
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
            'category_id' => 'required|exists:workplace_categories,id',
            'template_id' => 'nullable|exists:workplace_templates,id',
            'is_public' => 'boolean',
            'allow_external_sharing' => 'boolean',
            'max_members' => 'nullable|integer|min:1',
            'max_storage_mb' => 'nullable|integer|min:1',
        ];
    }
}
