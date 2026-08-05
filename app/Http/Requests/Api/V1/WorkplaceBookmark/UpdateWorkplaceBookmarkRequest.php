<?php

namespace App\Http\Requests\Api\V1\WorkplaceBookmark;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un favori d'espace de travail — D12.
 *
 * Pas de méthode d'édition dans `WorkplaceBookmarkController` (le store est un
 * toggle) : règles minimales conservées pour un usage futur.
 */
class UpdateWorkplaceBookmarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => 'nullable|string',
            'tags' => 'nullable|string|max:191',
        ];
    }
}
