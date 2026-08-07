<?php

namespace App\Http\Requests\Api\V1\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création d'un rôle — D09.
 *
 * Règles reprises de `RoleController::store()` (relu le 2026-08-05). La table
 * `roles` porte `description` et `guard_name` (NOT NULL DEFAULT 'web'), PAS
 * `display_name` : ne pas réintroduire ce champ. `name` est borné à 191 (longueur
 * réelle de la colonne, le `max:255` du Blade aurait déclenché une erreur SQL).
 */
class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191', Rule::unique('roles', 'name')],
            'description' => ['nullable', 'string'],
            'guard_name' => ['nullable', 'string', 'max:191'],
        ];
    }
}
