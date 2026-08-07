<?php

namespace App\Http\Requests\Api\V1\RecordContainer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Association notice ↔ contenant — D02.
 *
 * Règles reprises de `RecordContainerController::store()` (relu le 2026-08-04) :
 * le contenant est identifié par son `id` (existence vérifiée). `record_id` est
 * porté par la notice parente de la route, `creator_id` posé depuis l'agent.
 */
class StoreRecordContainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'container_id' => 'required|exists:containers,id',
            'description' => 'nullable|string|max:100',
        ];
    }
}
