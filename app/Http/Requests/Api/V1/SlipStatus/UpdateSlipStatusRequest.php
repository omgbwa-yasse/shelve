<?php

namespace App\Http\Requests\Api\V1\SlipStatus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un statut de bordereau — D04.
 *
 * Règles reprises de `SlipStatusController::update()` (relu le 2026-08-04).
 * L'unicité ignore le statut en cours de modification.
 */
class UpdateSlipStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'max:50', Rule::unique('slip_statuses', 'name')->ignore($this->route('slip_status'))],
            'description' => 'nullable',
        ];
    }
}
