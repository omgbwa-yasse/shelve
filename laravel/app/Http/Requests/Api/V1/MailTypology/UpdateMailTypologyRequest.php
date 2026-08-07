<?php

namespace App\Http\Requests\Api\V1\MailTypology;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une typologie de courrier — D06.
 *
 * Règles reprises de `MailTypologyController::update()` (relu le 2026-08-04),
 * avec l'unicité du nom ignorée sur l'enregistrement courant.
 */
class UpdateMailTypologyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|max:5',
            'name' => ['sometimes', 'required', 'max:50', Rule::unique('mail_typologies', 'name')->ignore($this->route('mail_typology'))],
            'description' => 'nullable|max:100',
            'activity_id' => 'sometimes|required|exists:activities,id',
        ];
    }
}
