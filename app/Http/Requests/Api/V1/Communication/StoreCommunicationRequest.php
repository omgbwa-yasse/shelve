<?php

namespace App\Http\Requests\Api\V1\Communication;

use App\Enums\CommunicationStatus;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une communication — D05.
 *
 * Règles reprises de `CommunicationController::store()` (relu le 2026-08-04).
 * `code` est généré serveur, `operator_id` / `operator_organisation_id` sont posés
 * depuis l'agent authentifié, jamais acceptés du client.
 */
class StoreCommunicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'return_date' => 'required|date|after_or_equal:today',
            'user_organisation_id' => 'required|exists:organisations,id',
            'status' => 'required|in:' . implode(',', array_map(fn ($s) => $s->value, CommunicationStatus::getAll())),
        ];
    }
}
