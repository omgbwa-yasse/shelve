<?php

namespace App\Http\Requests\Api\V1\ProjectResource;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string|in:human,financial,material,informational',
            'name' => 'required|string|max:190',
            'user_id' => 'nullable|integer|exists:users,id',
            'role' => 'nullable|string|max:190',
            'allocation_percent' => 'nullable|numeric|min:0|max:100',
            'unit_cost' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'planned_amount' => 'nullable|numeric|min:0',
            'actual_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ];
    }
}
