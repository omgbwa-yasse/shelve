<?php

namespace App\Http\Requests\Api\V1\RecordAttachment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Téléversement d'une pièce jointe de notice — D02.
 *
 * Règles reprises de `RecordAttachmentController::store()` (relu le 2026-08-04) :
 * fichier ≤ 100 Mo, MIME restreints. Le fichier est stocké, puis la pivot
 * `record_physical_attachment` est créée dans le contrôleur.
 */
class StoreRecordAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,mp4,avi,mov,txt,docx,doc,rtf,odt|max:102400',
            'name' => 'nullable|string|max:100',
            'thumbnail' => 'nullable|string',
        ];
    }
}
