<?php

namespace App\Http\Requests\Api\V1\WorkplaceConversation;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une conversation (chat) — D12.
 *
 * Pas de méthode `update` dans les contrôleurs Blade (le champ `updated_at` sert
 * de tri de chat) : règles minimales pour renommer un groupe/canal, réservé au
 * créateur dans le contrôleur.
 */
class UpdateWorkplaceConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:150',
            'description' => 'nullable|string',
        ];
    }
}
