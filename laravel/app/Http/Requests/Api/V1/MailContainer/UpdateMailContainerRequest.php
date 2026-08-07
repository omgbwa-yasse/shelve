<?php

namespace App\Http\Requests\Api\V1\MailContainer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un contenant de courrier — D06.
 *
 * Règles reprises de `MailContainerController::update()` (relu le 2026-08-04),
 * avec l'unicité du code ignorée sur l'enregistrement courant.
 */
class UpdateMailContainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', 'max:50', Rule::unique('mail_containers', 'code')->ignore($this->route('mail_container'))],
            'name' => 'sometimes|required|max:100',
            'property_id' => 'sometimes|required|exists:container_properties,id',
        ];
    }
}
