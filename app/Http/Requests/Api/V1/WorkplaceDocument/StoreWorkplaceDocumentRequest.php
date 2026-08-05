<?php

namespace App\Http\Requests\Api\V1\WorkplaceDocument;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Partage d'un document dans un espace de travail — D12.
 *
 * Règles reprises de `WorkplaceContentController::shareDocument()` (relu le
 * 2026-08-04). `shared_by` / `shared_at` posés côté serveur.
 */
class StoreWorkplaceDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_id' => 'required|exists:record_digital_documents,id',
            'access_level' => 'required|in:view,edit,full',
            'share_note' => 'nullable|string',
            'is_featured' => 'boolean',
        ];
    }
}
