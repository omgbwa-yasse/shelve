<?php

namespace App\Http\Requests\Api\V1\MailAction;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une action de courrier — D06.
 *
 * Règles reprises de `MailActionController::store()` (relu le 2026-08-04).
 */
class StoreMailActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|unique:mail_actions|max:100',
            'duration' => 'required|integer',
            'to_return' => 'nullable|boolean',
            'description' => 'required',
        ];
    }
}
