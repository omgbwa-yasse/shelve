<?php

namespace App\Http\Requests\Api\V1\KpiMeasurement;

use Illuminate\Foundation\Http\FormRequest;

class StoreKpiMeasurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required|numeric',
            'measured_at' => 'sometimes|date',
        ];
    }
}
