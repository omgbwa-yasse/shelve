<?php

namespace App\Http\Requests\Api\V1\Room;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une salle — D03.
 *
 * Règles reprises de `RoomController::update()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|max:10',
            'name' => 'sometimes|required|max:100',
            'description' => 'nullable',
            'visibility' => 'sometimes|required|in:public,private,inherit',
            'type' => 'sometimes|required|in:archives,producer',
            'floor_id' => 'sometimes|required|exists:floors,id',
        ];
    }
}
