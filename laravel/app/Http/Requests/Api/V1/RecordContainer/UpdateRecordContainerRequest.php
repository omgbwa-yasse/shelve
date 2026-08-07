<?php

namespace App\Http\Requests\Api\V1\RecordContainer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une association notice ↔ contenant — D02.
 */
class UpdateRecordContainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'sometimes|nullable|string|max:100',
        ];
    }
}
