<?php

namespace App\Http\Requests\Api\V1\Retention;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une durée de conservation — D07.
 *
 * Règles reprises de `RetentionController::update()` (relu le 2026-08-04),
 * passées en `sometimes` pour autoriser la mise à jour partielle (PATCH).
 */
class UpdateRetentionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|string|max:10',
            'name' => 'sometimes|string|max:200',
            'duration' => 'sometimes|integer',
            'sort_id' => 'sometimes|exists:sorts,id',
        ];
    }
}
