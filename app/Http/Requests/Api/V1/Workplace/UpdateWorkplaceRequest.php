<?php

namespace App\Http\Requests\Api\V1\Workplace;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un espace de travail — D12.
 *
 * Règles reprises de `WorkplaceController::update()` (relu le 2026-08-04), en
 * `sometimes`. `updated_by` est posé côté serveur.
 */
class UpdateWorkplaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:workplace_categories,id',
            'is_public' => 'boolean',
            'allow_external_sharing' => 'boolean',
            'max_members' => 'nullable|integer|min:1',
            'max_storage_mb' => 'nullable|integer|min:1',
        ];
    }
}
