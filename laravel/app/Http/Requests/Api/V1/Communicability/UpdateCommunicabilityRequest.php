<?php

namespace App\Http\Requests\Api\V1\Communicability;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une communicabilité — D01.
 *
 * Règles reconstituées depuis `CommunicabilityController::update()` (relu le
 * 2026-08-04).
 *
 * Défaut trouvé et NON reproduit : le contrôleur Blade validait la clé `decription`
 * (faute de frappe) au lieu de `description`. Comme Laravel n'ignore pas les clés
 * absentes des règles, `description` n'était donc jamais validé côté serveur et
 * passait brut via `$request->all()` — un champ requis en création devenait
 * silencieusement libre en modification. Corrigé ici en `description`.
 */
class UpdateCommunicabilityRequest extends FormRequest
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
                Rule::unique('communicabilities', 'code')->ignore($this->route('communicability')),
            ],
            'name' => ['sometimes', 'required', 'max:100'],
            'duration' => ['sometimes', 'required', 'integer'],
            'description' => ['nullable'],
        ];
    }
}
