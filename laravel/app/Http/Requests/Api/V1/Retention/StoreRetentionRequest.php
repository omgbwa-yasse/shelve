<?php

namespace App\Http\Requests\Api\V1\Retention;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une durée de conservation — D07.
 *
 * Règles reprises de `RetentionController::store()` (relu le 2026-08-04).
 */
class StoreRetentionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:200',
            'duration' => 'required|integer',
            'sort_id' => 'required|exists:sorts,id',
        ];
    }
}
