<?php

namespace App\Http\Requests\Api\V1\Shelf;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un rayonnage — D03.
 *
 * Règles reprises de `ShelfController::update()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class UpdateShelfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|max:30',
            'observation' => 'nullable',
            'face' => 'sometimes|required|numeric|max:10',
            'ear' => 'sometimes|required|numeric|max:10',
            'shelf' => 'sometimes|required|numeric|max:10',
            'shelf_length' => 'sometimes|required|numeric',
            'room_id' => 'sometimes|required|exists:rooms,id',
        ];
    }
}
