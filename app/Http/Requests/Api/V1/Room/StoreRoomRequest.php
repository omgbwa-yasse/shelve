<?php

namespace App\Http\Requests\Api\V1\Room;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une salle — D03.
 *
 * Règles reprises de `RoomController::store()` (relu le 2026-08-04). L'organisation
 * de rattachement (`organisation_room`) est posée depuis l'agent authentifié dans le
 * contrôleur, jamais acceptée du client.
 */
class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'nullable',
            'visibility' => 'required|in:public,private,inherit',
            'type' => 'required|in:archives,producer',
            'floor_id' => 'required|exists:floors,id',
        ];
    }
}
