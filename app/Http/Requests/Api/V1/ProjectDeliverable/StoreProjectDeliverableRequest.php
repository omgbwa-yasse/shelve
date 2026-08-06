<?php

namespace App\Http\Requests\Api\V1\ProjectDeliverable;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectDeliverableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'milestone_id' => 'nullable|integer|exists:project_milestones,id',
            'name' => 'required|string|max:190',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'attachment_id' => 'nullable|integer|exists:attachments,id',
        ];
    }
}
