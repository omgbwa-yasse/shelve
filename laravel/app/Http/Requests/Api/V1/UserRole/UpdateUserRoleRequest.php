<?php

namespace App\Http\Requests\Api\V1\UserRole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un rattachement agent→rôle (`user_roles`) — D09.
 *
 * Règles reprises de `UserRoleController::update()` (relu le 2026-08-05), passées
 * en `sometimes` (PATCH). L'unicité de la paire (`user_id`, `role_id`) est
 * contrôlée dans les deux sens, en s'ignorant soi-même.
 */
class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $current = $this->route('user_role');

        return [
            'user_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('user_roles', 'user_id')
                    ->where(fn ($q) => $q->where('role_id', $this->input('role_id', $current->role_id)))
                    ->ignore($current->id),
            ],
            'role_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:roles,id',
                Rule::unique('user_roles', 'role_id')
                    ->where(fn ($q) => $q->where('user_id', $this->input('user_id', $current->user_id)))
                    ->ignore($current->id),
            ],
        ];
    }
}
