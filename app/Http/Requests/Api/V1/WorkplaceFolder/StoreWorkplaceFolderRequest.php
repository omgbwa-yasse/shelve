<?php

namespace App\Http\Requests\Api\V1\WorkplaceFolder;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Partage d'un dossier dans un espace de travail — D12.
 *
 * Règles reprises de `WorkplaceContentController::shareFolder()` (relu le
 * 2026-08-04). `shared_by` / `shared_at` posés côté serveur.
 */
class StoreWorkplaceFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'folder_id' => 'required|exists:record_digital_folders,id',
            'access_level' => 'required|in:view,edit,full',
            'share_note' => 'nullable|string',
            'is_pinned' => 'boolean',
        ];
    }
}
