<?php

namespace App\Http\Requests\Api\V1\Objective;

use App\Models\Objective;
use Illuminate\Foundation\Http\FormRequest;

class UpdateObjectiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id' => 'nullable|integer|exists:projects,id',
            'title' => 'sometimes|string|max:190',
            'description' => 'nullable|string',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
            'status' => 'sometimes|string|in:on_track,at_risk,off_track,done',
            'owner_id' => 'nullable|integer|exists:users,id',
            'attachable_type' => 'sometimes|string|in:' . implode(',', array_keys(Objective::attachableAliases())),
            'attachable_id' => 'required_with:attachable_type|integer',
        ];
    }
}
