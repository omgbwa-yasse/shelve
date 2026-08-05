<?php

namespace App\Http\Requests\Api\V1\Reservation;

use App\Enums\ReservationStatus;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une réservation — D05.
 *
 * Règles reprises de `ReservationController::update()` (relu le 2026-08-04).
 * `code` reste généré serveur (le Blade l'acceptait en édition — écart documenté :
 * il n'est plus modifiable par le client, cohérent avec le `store`).
 */
class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:200',
            'content' => 'nullable|string',
            'user_id' => 'sometimes|required|exists:users,id',
            'user_organisation_id' => 'sometimes|required|exists:organisations,id',
            'status' => 'sometimes|required|in:' . implode(',', array_map(fn ($case) => $case->value, ReservationStatus::cases())),
        ];
    }
}
