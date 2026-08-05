<?php

namespace App\Http\Requests\Api\V1\Backup;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mise à jour d'une sauvegarde — D16.
 *
 * Règles reprises du `Validator` de `BackupController::update()`. `user_id`, `size`,
 * `backup_file` et `path` restent gérés serveur.
 */
class UpdateBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_time' => 'sometimes|date',
            'type' => 'sometimes|in:metadata,full',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:in_progress,success,failed',
        ];
    }
}
