<?php

namespace App\Http\Requests\Api\V1\MailArchive;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une archive de courrier — D06.
 *
 * Règles reprises de `MailArchiveController::update()` (relu le 2026-08-04).
 */
class UpdateMailArchiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'container_id' => 'sometimes|required|exists:mail_containers,id',
            'mail_id' => 'sometimes|required|exists:mails,id',
            'document_type' => 'sometimes|required|in:original,duplicate,copy',
        ];
    }
}
