<?php

namespace App\Http\Requests\Api\V1\Shelf;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un rayonnage — D03.
 *
 * Règles reprises de `ShelfController::store()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié, jamais accepté du client.
 */
class StoreShelfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|max:30',
            'observation' => 'nullable',
            'face' => 'required|numeric|max:10',
            'ear' => 'required|numeric|max:10',
            'shelf' => 'required|numeric|max:10',
            'shelf_length' => 'required|numeric',
            'room_id' => 'required|exists:rooms,id',
        ];
    }
}
