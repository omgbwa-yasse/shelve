<?php

namespace App\Http\Requests\Api\V1\SlipRecord;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un document de bordereau — D04.
 *
 * Règles reprises de `SlipRecordController::update()` (relu le 2026-08-04).
 * `date_format` est redérivé serveur de `date_start`/`date_end` comme en Blade.
 */
class UpdateSlipRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|string|max:10',
            'name' => 'sometimes|required|string',
            'date_start' => 'nullable|string|max:10',
            'date_end' => 'nullable|string|max:10',
            'date_exact' => 'nullable|date',
            'content' => 'nullable|string',
            'level_id' => 'sometimes|required|exists:record_levels,id',
            'width' => 'nullable|numeric',
            'width_description' => 'nullable|string|max:100',
            'support_id' => 'sometimes|required|exists:record_supports,id',
            'activity_id' => 'sometimes|required|exists:activities,id',
            'container_ids' => 'nullable|array',
            'container_ids.*' => 'exists:containers,id',
            'keywords' => 'nullable|string',
        ];
    }
}
