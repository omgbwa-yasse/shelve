<?php

namespace App\Http\Requests\Api\V1\Communication;

use App\Enums\CommunicationStatus;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une communication — D05.
 *
 * Règles reprises de `CommunicationController::update()` (relu le 2026-08-04).
 * `code` reste généré serveur (le Blade l'acceptait en édition — écart documenté :
 * il n'est plus modifiable par le client, cohérent avec le `store`).
 */
class UpdateCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'content' => 'nullable|string',
            'user_id' => 'sometimes|required|exists:users,id',
            'return_date' => 'sometimes|required|date',
            'user_organisation_id' => 'sometimes|required|exists:organisations,id',
            'status' => 'sometimes|required|in:' . implode(',', array_map(fn ($s) => $s->value, CommunicationStatus::getAll())),
        ];
    }
}
