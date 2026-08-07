<?php

namespace App\Http\Requests\Api\V1\UserOrganisationRole;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un rattachement agent→organisation→rôle — D09.
 *
 * L'identité de la ressource est la paire (`user_id`, `organisation_id`) portée
 * par l'URL — elle n'est pas modifiable ici (la clé primaire est composite).
 * Seul `role_id` est mutable, comme dans le Blade (l'update ne changeait que
 * l'activité… ici le rôle). `creator_id` reste celui d'origine.
 */
class UpdateUserOrganisationRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => ['sometimes', 'required', 'integer', 'exists:roles,id'],
        ];
    }
}
