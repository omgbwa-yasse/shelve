<?php

namespace App\Http\Requests\Api\V1\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|string|max:30|unique:projects,code,' . $this->route('project')?->id,
            'name' => 'sometimes|string|max:190',
            'description' => 'nullable|string',
            'status' => 'sometimes|string|in:draft,active,on_hold,completed,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'owner_id' => 'nullable|integer|exists:users,id',
            'attachable_type' => 'sometimes|string|in:' . implode(',', array_keys(Project::attachableAliases())),
            'attachable_id' => 'required_with:attachable_type|integer',
        ];
    }
}
