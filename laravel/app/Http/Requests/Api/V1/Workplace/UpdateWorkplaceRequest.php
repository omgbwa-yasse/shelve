<?php

namespace App\Http\Requests\Api\V1\Workplace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un espace de travail — D12.
 *
 * Règles reprises de `WorkplaceController::update()` (relu le 2026-08-04), en
 * `sometimes`. `code` (slug) reste modifiable mais unique (hors lui-même).
 * `updated_by` est posé côté serveur.
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
            'code' => [
                'sometimes',
                'string',
                'max:50',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/i',
                Rule::unique('workplaces', 'code')->ignore($this->route('workplace')),
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|nullable|exists:workplace_categories,id',
            'is_public' => 'boolean',
            'allow_external_sharing' => 'boolean',
            'max_members' => 'nullable|integer|min:1',
            'max_storage_mb' => 'nullable|integer|min:1',
        ];
    }
}
