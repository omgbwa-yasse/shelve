<?php

namespace App\Http\Requests\Api\V1\UserRole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création d'un rattachement agent→rôle (`user_roles`) — D09.
 *
 * Règles reprises de `UserRoleController::store()` (relu le 2026-08-05), plus la
 * contrainte d'unicité réelle de la table (UNIQUE `user_id` + `role_id`).
 */
class StoreUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
                Rule::unique('user_roles', 'role_id')->where(fn ($q) => $q->where('user_id', $this->input('user_id'))),
            ],
        ];
    }
}
