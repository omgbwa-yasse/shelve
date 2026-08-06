<?php

namespace App\Http\Requests\Api\V1\AiRoutine;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiRoutineRequest extends FormRequest
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
            'prompt_id' => 'nullable|required_without:skill_id|integer|exists:prompts,id',
            'skill_id' => 'nullable|required_without:prompt_id|integer|exists:ai_skills,id',
            'schedule_type' => 'required|string|in:once,hourly,daily,weekly',
            'run_time' => 'nullable|date_format:H:i|required_if:schedule_type,daily,weekly',
            'day_of_week' => 'nullable|integer|min:0|max:6|required_if:schedule_type,weekly',
            'is_enabled' => 'sometimes|boolean',
        ];
    }
}
