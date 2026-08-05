<?php

namespace App\Http\Requests\Api\V1\ReservationRecord;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un document de réservation — D05.
 *
 * Règles reprises de `ReservationRecordController::update()` (relu le 2026-08-04).
 */
class UpdateReservationRecordRequest extends FormRequest
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
            'reservation_date' => 'sometimes|required|date',
        ];
    }
}
