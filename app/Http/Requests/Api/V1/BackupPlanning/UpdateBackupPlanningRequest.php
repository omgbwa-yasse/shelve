<?php

namespace App\Http\Requests\Api\V1\BackupPlanning;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mise à jour d'une planification de sauvegarde — D16.
 *
 * Règles reprises du `Validator` de `BackupPlanningController::update()`, passées en
 * `sometimes` (mise à jour partielle).
 */
class UpdateBackupPlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'backup_id' => 'sometimes|integer|exists:backups,id',
            'frequence' => 'sometimes|in:daily,weekly,monthly',
            'week_day' => 'sometimes|required_if:frequence,weekly|nullable|integer|between:1,7',
            'month_day' => 'sometimes|required_if:frequence,monthly|nullable|integer|between:1,31',
            'hour' => 'sometimes|date_format:H:i',
        ];
    }
}
