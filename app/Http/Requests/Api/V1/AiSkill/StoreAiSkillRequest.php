<?php

namespace App\Http\Requests\Api\V1\AiSkill;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une compétence IA — D14.
 *
 * Le Blade crée les skills par `install` (ZIP, TODO E2) : le CRUD ci-dessous reprend
 * les contraintes du schéma. `installed_by` est géré serveur (agent authentifié).
 */
class StoreAiSkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required|string|max:191|unique:ai_skills',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'version' => 'nullable|string|max:50',
            'location' => 'sometimes|in:system,custom',
            'folder' => 'nullable|string|max:191',
            'enabled' => 'sometimes|boolean',
        ];
    }
}
