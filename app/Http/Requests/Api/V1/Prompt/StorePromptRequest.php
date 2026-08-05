<?php

namespace App\Http\Requests\Api\V1\Prompt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création d'un prompt — D14.
 *
 * Règles reprises de `PromptManagementController::store()`. `organisation_id` (org
 * courante) et `user_id` (agent) sont posés côté serveur. Unicité conditionnelle de
 * `title` sur (is_system, organisation, user), comme en Blade.
 */
class StorePromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('prompts')->where(function ($query) {
                    return $query->where('is_system', $this->boolean('is_system'))
                        ->where('organisation_id', $this->user()?->current_organisation_id)
                        ->where('user_id', $this->user()?->id);
                }),
            ],
            'content' => 'required|string',
            'is_system' => 'sometimes|boolean',
        ];
    }
}
