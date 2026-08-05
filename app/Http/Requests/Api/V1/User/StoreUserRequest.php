<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un agent — D09.
 *
 * Règles reprises de `UserController::store()` (relu le 2026-08-05).
 * `users.birthday` est NOT NULL sans défaut : requis. `password` est haché par le
 * cast `hashed` du modèle (jamais de hachage côté client). `email_verified_at` et
 * `current_organisation_id` ne sont PAS acceptés du client : l'organisation
 * courante se bascule via `auth/switch-organisation`. La confirmation de mot de
 * passe (`confirmed`) est une contrainte de formulaire web, sans équivalent API.
 */
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'surname' => ['nullable', 'string', 'max:191'],
            'birthday' => ['required', 'date'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
