<?php

namespace App\Http\Requests\Api\V1\Slip;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un bordereau — D04.
 *
 * Règles reprises de `SlipController::update()` (relu le 2026-08-04).
 * `officer_id` / `officer_organisation_id` sont posés depuis l'agent authentifié.
 */
class UpdateSlipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|max:20',
            'name' => 'sometimes|required|max:200',
            'description' => 'nullable',
            'user_organisation_id' => 'sometimes|required|exists:organisations,id',
            'user_id' => 'nullable|exists:users,id',
            'slip_status_id' => 'sometimes|required|exists:slip_statuses,id',
            'is_received' => 'nullable|boolean',
            'received_date' => 'nullable|date',
            'is_approved' => 'nullable|boolean',
            'approved_date' => 'nullable|date',
        ];
    }
}
