<?php

namespace App\Http\Requests\Api\V1\BackupFile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un fichier de sauvegarde — D16.
 *
 * Règles reprises du `Validator` de `BackupFileController::store()`.
 */
class StoreBackupFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'backup_id' => 'required|integer|exists:backups,id',
            'path_original' => 'required|string',
            'path_storage' => 'required|string',
            'size' => 'required|integer',
            'hash' => 'required|string|size:150',
        ];
    }
}
