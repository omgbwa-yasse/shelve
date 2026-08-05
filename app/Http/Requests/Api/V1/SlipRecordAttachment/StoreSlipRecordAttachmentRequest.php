<?php

namespace App\Http\Requests\Api\V1\SlipRecordAttachment;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Upload d'une pièce jointe de document de bordereau — D04.
 *
 * Règles reprises de `slipRecordAttachmentController::upload()` (relu le 2026-08-04).
 * La création d'une pivot passe exclusivement par l'upload (pas de `store` générique) ;
 * `slip_record_id` vient de la route et `creator_id` de l'agent authentifié.
 */
class StoreSlipRecordAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'thumbnail' => 'nullable|string',
        ];
    }
}
