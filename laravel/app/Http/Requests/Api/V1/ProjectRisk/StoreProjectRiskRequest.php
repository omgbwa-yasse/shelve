<?php

namespace App\Http\Requests\Api\V1\ProjectRisk;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRiskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_id' => 'nullable|exists:tasks,id',
            'title' => 'required|string|max:190',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|in:technical,financial,schedule,resource,external,other',
            'probability' => 'sometimes|string|in:low,medium,high',
            'impact' => 'sometimes|string|in:low,medium,high',
            'mitigation_plan' => 'nullable|string',
            'owner_id' => 'nullable|exists:users,id',
            'review_date' => 'nullable|date',
        ];
    }
}
