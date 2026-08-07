<?php

namespace App\Http\Requests\Api\V1\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une langue — D01.
 *
 * Règles reconstituées depuis `LanguageController::update()` (relu le 2026-08-04).
 *
 * Écart trouvé avec le contrôleur Blade d'origine : celui-ci validait `code` en
 * `max:3`, alors que la colonne est `varchar(2)` (confirmé sur le schéma) et que
 * `store()` valide bien `max:2`. Un code de 3 caractères en modification aurait
 * provoqué une troncature silencieuse ou une erreur SQL selon le mode strict,
 * jamais un 422 propre. La contrainte du schéma fait foi ici.
 */
class UpdateLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes', 'required', 'string', 'max:2',
                Rule::unique('languages', 'code')->ignore($this->route('language')),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:50'],
        ];
    }
}
