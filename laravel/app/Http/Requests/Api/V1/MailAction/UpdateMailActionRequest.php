<?php

namespace App\Http\Requests\Api\V1\MailAction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une action de courrier — D06.
 *
 * Règles reprises de `MailActionController::update()` (relu le 2026-08-04),
 * avec l'unicité du nom ignorée sur l'enregistrement courant.
 */
class UpdateMailActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'max:100', Rule::unique('mail_actions', 'name')->ignore($this->route('mail_action'))],
            'duration' => 'sometimes|required|integer',
            'to_return' => 'nullable|boolean',
            'description' => 'sometimes|required',
        ];
    }
}
