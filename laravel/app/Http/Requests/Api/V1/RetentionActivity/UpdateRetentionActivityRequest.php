<?php

namespace App\Http\Requests\Api\V1\RetentionActivity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une liaison activité ↔ durée de conservation — D07.
 *
 * Non exposée en API (clé composite sans `id`) : conservée pour cohérence du jeu de
 * fichiers. L'unicité de la paire ignore la paire courante portée par la route.
 */
class UpdateRetentionActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'retention_id' => ['sometimes', 'integer', 'exists:retentions,id'],
            'activity_id' => [
                'sometimes',
                'integer',
                'exists:activities,id',
                Rule::unique('retention_activity')
                    ->ignore($this->route('activity'))
                    ->where(fn ($q) => $q->where('retention_id', $this->input('retention_id', $this->route('retention')))),
            ],
        ];
    }
}
