<?php

namespace App\Http\Requests\Api\V1\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un rôle — D09.
 *
 * Règles reprises de `RoleController::update()` (relu le 2026-08-05), passées en
 * `sometimes` (PATCH) avec `Rule::unique()->ignore()`. Pas de `display_name`.
 */
class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:191',
                Rule::unique('roles', 'name')->ignore($this->route('role')->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'guard_name' => ['sometimes', 'nullable', 'string', 'max:191'],
        ];
    }
}
