<?php

namespace App\Http\Requests\Api\V1\UserOrganisationRole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création d'un rattachement agent→organisation→rôle — D09.
 *
 * Règles reprises de `UserOrganisationRoleController::store()` (relu le 2026-08-05).
 * `creator_id` (NOT NULL) est posé depuis l'agent authentifié dans le contrôleur,
 * JAMAIS accepté du client. L'unicité de la paire (`user_id`, `organisation_id`)
 * — clé primaire composite — est contrôlée en validation. Le contrôleur re-vérifie
 * que l'organisation cible est l'organisation courante (R03).
 */
class StoreUserOrganisationRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('user_organisation_role', 'user_id')
                    ->where(fn ($q) => $q->where('organisation_id', $this->input('organisation_id'))),
            ],
            'organisation_id' => ['required', 'integer', 'exists:organisations,id'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];
    }
}
