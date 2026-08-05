<?php

namespace App\Http\Requests\Api\V1\WorkplaceConversation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une conversation (chat) — D12.
 *
 * Règles reprises de `WorkplaceMessageController::store()` et `ChatController::store()`
 * (relus le 2026-08-04). `workplace_id` NULL → conversation globale (chat direct) ;
 * sinon conversation rattachée à un espace de travail. `created_by` posé côté
 * serveur ; `participant_ids` unifie les `user_id` / `participant_ids` des deux
 * contrôleurs Blade.
 */
class StoreWorkplaceConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workplace_id' => 'nullable|integer|exists:workplaces,id',
            'type' => 'required|in:group,channel,private',
            'name' => 'required_if:type,group,channel|max:150',
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:users,id',
        ];
    }
}
