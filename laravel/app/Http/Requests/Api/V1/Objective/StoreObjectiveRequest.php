<?php

namespace App\Http\Requests\Api\V1\Objective;

use App\Models\Objective;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un objectif (OKR) — D17. `project_id` est nullable : un OKR
 * d'équipe/personne n'a pas toujours de projet formel derrière lui.
 */
class StoreObjectiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'nullable|integer|exists:projects,id',
            'task_id' => 'nullable|integer|exists:tasks,id',
            'title' => 'required|string|max:190',
            'description' => 'nullable|string',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'status' => 'sometimes|string|in:on_track,at_risk,off_track,done',
            'owner_id' => 'nullable|integer|exists:users,id',
            'attachable_type' => 'nullable|string|in:' . implode(',', array_keys(Objective::attachableAliases())),
            'attachable_id' => 'nullable|integer',
            'key_results' => 'sometimes|array',
            'key_results.*.title' => 'required_with:key_results|string|max:190',
            'key_results.*.metric_type' => 'sometimes|string|in:number,percentage,currency,boolean',
            'key_results.*.start_value' => 'sometimes|numeric',
            'key_results.*.target_value' => 'required_with:key_results|numeric',
            'key_results.*.unit' => 'nullable|string|max:30',
            'key_results.*.due_date' => 'nullable|date',
        ];
    }
}
