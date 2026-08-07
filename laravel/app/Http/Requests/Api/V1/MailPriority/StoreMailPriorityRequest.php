<?php

namespace App\Http\Requests\Api\V1\MailPriority;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une priorité de courrier — D06.
 *
 * Règles reprises de `MailPriorityController::store()` (relu le 2026-08-04).
 */
class StoreMailPriorityRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|unique:mail_priorities|max:50',
            'duration' => 'required|integer',
        ];
    }
}
