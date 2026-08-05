<?php

namespace App\Http\Requests\Api\V1\RecordReactivation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une demande de réactivation — D02.
 *
 * Règles reprises de `RecordReactivationController::store()` (relu le 2026-08-04).
 * `record_id` est porté par la notice parente de la route, `previous_status_id`,
 * `requested_by` et `requested_date` sont posés serveur depuis l'agent et la notice.
 */
class StoreRecordReactivationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string',
            'new_transfer_date' => 'nullable|date',
        ];
    }
}
