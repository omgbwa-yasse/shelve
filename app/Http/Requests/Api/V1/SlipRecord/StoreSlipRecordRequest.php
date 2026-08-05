<?php

namespace App\Http\Requests\Api\V1\SlipRecord;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un document de bordereau — D04.
 *
 * Règles reprises de `SlipRecordController::store()` (relu le 2026-08-04).
 * `slip_id` vient de la route, `creator_id` de l'agent authentifié, et `date_format`
 * est dérivé serveur de `date_start`/`date_end` (jamais accepté du client).
 */
class StoreSlipRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:10',
            'name' => 'required|string',
            'date_start' => 'nullable|string|max:10',
            'date_end' => 'nullable|string|max:10',
            'date_exact' => 'nullable|date',
            'content' => 'nullable|string',
            'level_id' => 'required|exists:record_levels,id',
            'width' => 'nullable|numeric',
            'width_description' => 'nullable|string|max:100',
            'support_id' => 'required|exists:record_supports,id',
            'activity_id' => 'required|exists:activities,id',
            'container_id' => 'nullable|exists:containers,id',
            'keywords' => 'nullable|string',
        ];
    }
}
