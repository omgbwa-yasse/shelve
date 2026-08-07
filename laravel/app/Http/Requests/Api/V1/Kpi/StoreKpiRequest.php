<?php

namespace App\Http\Requests\Api\V1\Kpi;

use App\Models\Kpi;
use Illuminate\Foundation\Http\FormRequest;

class StoreKpiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:30|unique:kpis,code',
            'name' => 'required|string|max:190',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:30',
            'target_value' => 'nullable|numeric',
            'direction' => 'sometimes|string|in:higher_is_better,lower_is_better',
            'frequency' => 'sometimes|string|in:daily,weekly,monthly,quarterly,yearly',
            'task_id' => 'nullable|integer|exists:tasks,id',
            'owner_id' => 'nullable|integer|exists:users,id',
            'attachable_type' => 'nullable|string|in:' . implode(',', array_keys(Kpi::attachableAliases())),
            'attachable_id' => 'nullable|integer',
        ];
    }
}
