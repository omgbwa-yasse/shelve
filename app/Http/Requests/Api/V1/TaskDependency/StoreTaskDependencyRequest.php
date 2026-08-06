<?php

namespace App\Http\Requests\Api\V1\TaskDependency;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskDependencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'successor_id' => 'required|integer|exists:tasks,id',
            'type' => 'sometimes|string|in:finish_to_start,start_to_start,finish_to_finish,start_to_finish',
            'lag_days' => 'nullable|integer',
        ];
    }
}
