<?php

namespace App\Http\Requests\Api\V1\ProjectMilestone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:190',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'sometimes|string|in:pending,reached,missed',
            'sort_order' => 'sometimes|integer',
        ];
    }
}
