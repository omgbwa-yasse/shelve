<?php

namespace App\Http\Requests\Api\V1\Prompt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Mise à jour d'un prompt — D14.
 *
 * Règles reprises de `PromptManagementController::update()` (avec exclusion de la
 * ressource courante dans l'unicité conditionnelle). `organisation_id` et `user_id`
 * restent gérés serveur.
 */
class UpdatePromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('prompts')->where(function ($query) {
                    return $query->where('is_system', $this->boolean('is_system'))
                        ->where('organisation_id', $this->user()?->current_organisation_id)
                        ->where('user_id', $this->user()?->id);
                })->ignore($this->route('prompt')),
            ],
            'content' => 'sometimes|string',
            'is_system' => 'sometimes|boolean',
        ];
    }
}
