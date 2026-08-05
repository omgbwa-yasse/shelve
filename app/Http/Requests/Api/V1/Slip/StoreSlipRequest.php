<?php

namespace App\Http\Requests\Api\V1\Slip;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un bordereau — D04.
 *
 * Règles reprises de `SlipController::store()` (relu le 2026-08-04).
 * `officer_id`, `officer_organisation_id` et `slip_status_id` (statut par défaut
 * « Projects ») sont posés depuis l'agent authentifié, jamais acceptés du client.
 */
class StoreSlipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|max:20',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'user_organisation_id' => 'required|exists:organisations,id',
            'user_id' => 'nullable|exists:users,id',
            'is_received' => 'nullable|boolean',
            'received_date' => 'nullable|date',
            'is_approved' => 'nullable|boolean',
            'approved_date' => 'nullable|date',
        ];
    }
}
