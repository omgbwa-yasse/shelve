<?php

namespace App\Http\Requests\Api\V1\WorkplaceFolder;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un partage de dossier — D12.
 *
 * Pas de méthode d'édition dans `WorkplaceContentController` : règles minimales
 * conservées pour un usage futur.
 */
class UpdateWorkplaceFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'access_level' => 'sometimes|required|in:view,edit,full',
            'share_note' => 'nullable|string',
            'is_pinned' => 'boolean',
        ];
    }
}
