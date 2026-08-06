<?php

namespace App\Http\Requests\Api\V1\ProjectStatusReport;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectStatusReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reported_at' => 'nullable|date',
            'percent_complete' => 'required|integer|min:0|max:100',
            'summary' => 'required|string',
            'risks' => 'nullable|string',
        ];
    }
}
