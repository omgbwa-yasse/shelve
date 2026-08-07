<?php

namespace App\Http\Requests\Api\V1\WorkplaceBookmark;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un favori d'espace de travail — D12.
 *
 * Règles reprises de `WorkplaceBookmarkController::store()` (relu le 2026-08-04).
 * `workplace_id` et `user_id` sont posés côté serveur.
 */
class StoreWorkplaceBookmarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bookmarkable_type' => 'required|string',
            'bookmarkable_id' => 'required|integer',
            'note' => 'nullable|string',
        ];
    }
}
