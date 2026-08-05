<?php

namespace App\Http\Requests\Api\V1\SlipRecordContainer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une association document↔contenant — D04.
 *
 * Règles reprises de `SlipRecordContainerController::update()` (relu le 2026-08-04).
 * `creator_id` est posé depuis l'agent authentifié.
 */
class UpdateSlipRecordContainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'container_id' => 'sometimes|required|exists:containers,id',
            'description' => 'sometimes|required|string|max:200',
        ];
    }
}
