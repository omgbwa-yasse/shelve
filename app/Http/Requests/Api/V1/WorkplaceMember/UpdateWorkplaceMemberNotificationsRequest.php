<?php

namespace App\Http\Requests\Api\V1\WorkplaceMember;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Préférences de notification d'un membre — D12.
 *
 * Règles reprises de `WorkplaceMemberController::updateNotifications()` (relu le 2026-08-04).
 */
class UpdateWorkplaceMemberNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'notify_on_new_content' => 'boolean',
            'notify_on_mentions' => 'boolean',
            'notify_on_updates' => 'boolean',
        ];
    }
}
