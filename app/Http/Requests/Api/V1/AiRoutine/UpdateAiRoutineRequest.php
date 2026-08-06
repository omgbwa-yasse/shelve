<?php

namespace App\Http\Requests\Api\V1\AiRoutine;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiRoutineRequest extends FormRequest
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
            'prompt_id' => 'nullable|integer|exists:prompts,id',
            'skill_id' => 'nullable|integer|exists:ai_skills,id',
            'schedule_type' => 'sometimes|string|in:once,hourly,daily,weekly',
            'run_time' => 'nullable|date_format:H:i',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'is_enabled' => 'sometimes|boolean',
        ];
    }
}
