<?php

namespace App\Http\Requests\Api\V1\KeyResult;

use Illuminate\Foundation\Http\FormRequest;

class StoreKeyResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:190',
            'metric_type' => 'sometimes|string|in:number,percentage,currency,boolean',
            'start_value' => 'sometimes|numeric',
            'target_value' => 'required|numeric',
            'current_value' => 'sometimes|numeric',
            'unit' => 'nullable|string|max:30',
            'status' => 'sometimes|string|in:on_track,at_risk,off_track,done',
            'due_date' => 'nullable|date',
            'sort_order' => 'sometimes|integer',
        ];
    }
}
