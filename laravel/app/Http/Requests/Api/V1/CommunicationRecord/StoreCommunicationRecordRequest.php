<?php

namespace App\Http\Requests\Api\V1\CommunicationRecord;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un document de communication — D05.
 *
 * Règles reprises de `CommunicationRecordController::store()` (relu le 2026-08-04).
 * `communication_id` vient de la route, `return_date` est calculé serveur (+14 jours).
 */
class StoreCommunicationRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'record_id' => 'required|exists:records,id',
            'is_original' => 'required|in:0,1',
            'content' => 'nullable|string',
        ];
    }
}
