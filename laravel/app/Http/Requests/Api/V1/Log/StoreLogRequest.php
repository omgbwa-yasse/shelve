<?php

namespace App\Http\Requests\Api\V1\Log;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une entrée de journal — D16.
 *
 * Règles reprises de `LogController::store()`. `user_id` est posé depuis l'agent
 * authentifié ; `ip_address` et `user_agent` sont prélevés sur la requête si absents.
 */
class StoreLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
        ];
    }
}
