<?php

namespace App\Http\Requests\Api\V1\ProjectMilestone;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:190',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'sort_order' => 'sometimes|integer',
        ];
    }
}
