<?php

namespace App\Http\Requests\Api\V1\ProjectDeliverable;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectDeliverableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'milestone_id' => 'nullable|integer|exists:project_milestones,id',
            'name' => 'sometimes|string|max:190',
            'description' => 'nullable|string',
            'status' => 'sometimes|string|in:draft,submitted,approved,rejected',
            'due_date' => 'nullable|date',
            'attachment_id' => 'nullable|integer|exists:attachments,id',
        ];
    }
}
