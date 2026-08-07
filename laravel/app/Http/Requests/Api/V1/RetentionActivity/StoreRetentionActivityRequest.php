<?php

namespace App\Http\Requests\Api\V1\RetentionActivity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Rattachement d'une durée de conservation à une activité — D07.
 *
 * Règles reprises de `RetentionActivityController::store()` (relu le 2026-08-04) :
 * `retention_id` était obligatoire, `activity_id` venait de la route.
 * L'unicité de la paire est garantie par `firstOrCreate` dans le contrôleur.
 */
class StoreRetentionActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'retention_id' => ['required', 'integer', 'exists:retentions,id'],
            'activity_id' => [
                'required',
                'integer',
                'exists:activities,id',
                Rule::unique('retention_activity')->where(fn ($q) => $q->where('retention_id', $this->input('retention_id'))),
            ],
        ];
    }
}
