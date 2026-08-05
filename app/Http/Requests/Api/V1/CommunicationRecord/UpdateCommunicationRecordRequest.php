<?php

namespace App\Http\Requests\Api\V1\CommunicationRecord;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un document de communication — D05.
 *
 * Règles reprises de `CommunicationRecordController::update()` (relu le 2026-08-04).
 */
class UpdateCommunicationRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'record_id' => 'sometimes|required|exists:records,id',
            'is_original' => 'sometimes|required|boolean',
            'content' => 'nullable|string',
        ];
    }
}
