<?php

namespace App\Http\Requests\Api\V1\BackupFile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mise à jour d'un fichier de sauvegarde — D16.
 *
 * Règles reprises du `Validator` de `BackupFileController::update()`, passées en
 * `sometimes` (mise à jour partielle).
 */
class UpdateBackupFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'backup_id' => 'sometimes|integer|exists:backups,id',
            'path_original' => 'sometimes|string',
            'path_storage' => 'sometimes|string',
            'size' => 'sometimes|integer',
            'hash' => 'sometimes|string|size:150',
        ];
    }
}
