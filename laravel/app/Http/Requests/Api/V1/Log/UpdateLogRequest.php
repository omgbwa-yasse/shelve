<?php

namespace App\Http\Requests\Api\V1\Log;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mise à jour d'une entrée de journal — D16.
 *
 * Règles reprises de `LogController::update()`. `user_id` reste géré serveur.
 */
class UpdateLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'ip_address' => 'nullable|ip',
            'user_agent' => 'nullable|string',
        ];
    }
}
