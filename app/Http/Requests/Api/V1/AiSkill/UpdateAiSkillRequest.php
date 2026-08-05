<?php

namespace App\Http\Requests\Api\V1\AiSkill;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Mise à jour d'une compétence IA — D14.
 *
 * `installed_by` reste géré serveur. Unicité de `slug` avec exclusion de la ressource
 * courante (route model binding, paramètre `{ai_skill}`).
 */
class UpdateAiSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['sometimes', 'string', 'max:191', Rule::unique('ai_skills', 'slug')->ignore($this->route('ai_skill'))],
            'name' => 'sometimes|string|max:200',
            'description' => 'sometimes|nullable|string',
            'version' => 'sometimes|nullable|string|max:50',
            'location' => 'sometimes|in:system,custom',
            'folder' => 'sometimes|nullable|string|max:191',
            'enabled' => 'sometimes|boolean',
        ];
    }
}
