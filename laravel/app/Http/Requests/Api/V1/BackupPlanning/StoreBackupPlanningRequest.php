<?php

namespace App\Http\Requests\Api\V1\BackupPlanning;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une planification de sauvegarde — D16.
 *
 * Règles reprises du `Validator` de `BackupPlanningController::store()`.
 */
class StoreBackupPlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'backup_id' => 'required|integer|exists:backups,id',
            'frequence' => 'required|in:daily,weekly,monthly',
            'week_day' => 'required_if:frequence,weekly|nullable|integer|between:1,7',
            'month_day' => 'required_if:frequence,monthly|nullable|integer|between:1,31',
            'hour' => 'required|date_format:H:i',
        ];
    }
}
