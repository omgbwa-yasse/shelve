<?php

namespace App\Http\Requests\Api\V1\WorkplaceMember;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mise à jour des permissions fines d'un membre — D12.
 *
 * Règles reprises de `WorkplaceMemberController::updatePermissions()` (relu le 2026-08-04).
 */
class UpdateWorkplaceMemberPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'can_create_folders' => 'boolean',
            'can_create_documents' => 'boolean',
            'can_delete' => 'boolean',
            'can_share' => 'boolean',
            'can_invite' => 'boolean',
        ];
    }
}
