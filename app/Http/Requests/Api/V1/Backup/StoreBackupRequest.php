<?php

namespace App\Http\Requests\Api\V1\Backup;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une sauvegarde — D16.
 *
 * Règles reprises du `Validator` de `BackupController::update()` (le `store` du Blade
 * n'en portait aucune). `date_time`, `user_id`, `size`, `backup_file` et `path` sont
 * gérés serveur (génération de la sauvegarde, agent authentifié) — absents ici.
 */
class StoreBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:metadata,full',
            'description' => 'nullable|string',
            'status' => 'required|in:in_progress,success,failed',
        ];
    }
}
