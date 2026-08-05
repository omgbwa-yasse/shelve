<?php

namespace App\Http\Requests\Api\V1\Activity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une activité — D01.
 *
 * Règles reconstituées depuis `ActivityController::update()` (relu le 2026-08-04).
 */
class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes', 'required', 'max:10',
                Rule::unique('activities', 'code')->ignore($this->route('activity')),
            ],
            'name' => ['sometimes', 'required', 'max:100'],
            'observation' => ['nullable'],
            'parent_id' => ['nullable', 'exists:activities,id'],
            // Absent du contrôleur Blade d'origine (jamais validé ni assigné en
            // modification) ; conservé car la colonne existe, porte une clé étrangère
            // vers `communicabilities`, et l'API expose ce que le schéma permet
            // d'écrire — pas seulement ce que Blade en faisait.
            'communicability_id' => ['sometimes', 'nullable', 'integer', 'exists:communicabilities,id'],
        ];
    }
}
