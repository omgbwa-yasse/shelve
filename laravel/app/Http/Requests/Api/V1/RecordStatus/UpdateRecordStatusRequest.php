<?php

namespace App\Http\Requests\Api\V1\RecordStatus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un statut de notice — D02.
 *
 * Règles reprises de `RecordStatusController::update()` (relu le 2026-08-04),
 * passées en `sometimes` et ignorantes de la ressource courante.
 */
class UpdateRecordStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'max:100',
                Rule::unique('record_statuses', 'name')->ignore($this->route('record_status')),
            ],
            'description' => 'sometimes|nullable',
        ];
    }
}
