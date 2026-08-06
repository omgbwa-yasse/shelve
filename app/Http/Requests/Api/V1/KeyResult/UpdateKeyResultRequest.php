<?php

namespace App\Http\Requests\Api\V1\KeyResult;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mise à jour d'un résultat clé — couvre à la fois l'édition complète
 * (formulaire) et la mise à jour rapide de la progression (`current_value`
 * seul, écran de suivi OKR, cf. `evolution/PROJECT-OKR-KPI-PLAN.md` §3).
 */
class UpdateKeyResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:190',
            'metric_type' => 'sometimes|string|in:number,percentage,currency,boolean',
            'start_value' => 'sometimes|numeric',
            'target_value' => 'sometimes|numeric',
            'current_value' => 'sometimes|numeric',
            'unit' => 'nullable|string|max:30',
            'status' => 'sometimes|string|in:on_track,at_risk,off_track,done',
            'due_date' => 'nullable|date',
            'sort_order' => 'sometimes|integer',
        ];
    }
}
