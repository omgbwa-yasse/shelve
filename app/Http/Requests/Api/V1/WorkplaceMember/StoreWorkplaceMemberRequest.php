<?php

namespace App\Http\Requests\Api\V1\WorkplaceMember;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Ajout d'un membre ou envoi d'une invitation — D12.
 *
 * Règles reprises de `WorkplaceMemberController::store()` (relu le 2026-08-04).
 * `invited_by`, `joined_at` et les flags de permission sont posés côté serveur.
 */
class StoreWorkplaceMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email',
            'role' => 'required|in:admin,editor,contributor,viewer',
            'message' => 'nullable|string',
        ];
    }
}
