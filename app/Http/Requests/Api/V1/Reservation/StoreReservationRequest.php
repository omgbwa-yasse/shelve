<?php

namespace App\Http\Requests\Api\V1\Reservation;

use App\Enums\ReservationStatus;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une réservation — D05.
 *
 * Règles reprises de `ReservationController::store()` (relu le 2026-08-04).
 * `code` est généré serveur, `operator_id` / `operator_organisation_id` sont posés
 * depuis l'agent authentifié, jamais acceptés du client.
 */
class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'content' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'user_organisation_id' => 'required|exists:organisations,id',
            'status' => 'required|in:' . implode(',', array_map(fn ($case) => $case->value, ReservationStatus::cases())),
        ];
    }
}
