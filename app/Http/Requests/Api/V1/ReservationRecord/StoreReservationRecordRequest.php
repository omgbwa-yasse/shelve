<?php

namespace App\Http\Requests\Api\V1\ReservationRecord;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un document de réservation — D05.
 *
 * Règles reprises de `ReservationRecordController::store()` (relu le 2026-08-04).
 * `reservation_id` vient de la route et `operator_id` est posé depuis l'agent.
 */
class StoreReservationRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'record_id' => 'required|exists:records,id',
            'is_original' => 'required|boolean',
            'reservation_date' => 'required|date',
        ];
    }
}
