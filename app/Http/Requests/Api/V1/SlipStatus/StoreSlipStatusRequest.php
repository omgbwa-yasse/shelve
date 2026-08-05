<?php

namespace App\Http\Requests\Api\V1\SlipStatus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création d'un statut de bordereau — D04.
 *
 * Règles reprises de `SlipStatusController::store()` (relu le 2026-08-04).
 */
class StoreSlipStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:50', Rule::unique('slip_statuses', 'name')],
            'description' => 'nullable',
        ];
    }
}
