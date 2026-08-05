<?php

namespace App\Http\Requests\Api\V1\MailPriority;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une priorité de courrier — D06.
 *
 * Règles reprises de `MailPriorityController::update()` (relu le 2026-08-04),
 * avec l'unicité du nom ignorée sur l'enregistrement courant.
 */
class UpdateMailPriorityRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'max:50', Rule::unique('mail_priorities', 'name')->ignore($this->route('mail_priority'))],
            'duration' => 'sometimes|required|integer',
        ];
    }
}
