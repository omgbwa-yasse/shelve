<?php

namespace App\Http\Requests\Api\V1\RecordReactivation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Rejet d'une demande de réactivation — D02.
 *
 * Règles reprises de `RecordReactivationController::reject()` (relu le 2026-08-04).
 */
class UpdateRecordReactivationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string',
        ];
    }
}
