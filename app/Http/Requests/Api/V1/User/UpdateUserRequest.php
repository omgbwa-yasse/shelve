<?php

namespace App\Http\Requests\Api\V1\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un agent — D09.
 *
 * Règles reprises de `UserController::update()` (relu le 2026-08-05), passées en
 * `sometimes` (PATCH). `password` absent → inchangé ; présent → re-haché par le
 * cast `hashed` du modèle. `current_organisation_id` reste géré par
 * `auth/switch-organisation`, jamais accepté ici.
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:191'],
            'surname' => ['sometimes', 'nullable', 'string', 'max:191'],
            'birthday' => ['sometimes', 'required', 'date'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($this->route('user')->id),
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
        ];
    }
}
