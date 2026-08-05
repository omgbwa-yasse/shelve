<?php

namespace App\Http\Requests\Api\V1\WorkplaceMember;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Changement de rôle d'un membre — D12.
 *
 * Règles reprises de `WorkplaceMemberController::update()` (relu le 2026-08-04).
 * Les flags de permission dérivés du rôle sont posés côté serveur.
 */
class UpdateWorkplaceMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => 'sometimes|required|in:admin,editor,contributor,viewer',
        ];
    }
}
