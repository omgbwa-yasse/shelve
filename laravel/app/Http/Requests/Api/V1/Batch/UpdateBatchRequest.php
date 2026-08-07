<?php

namespace App\Http\Requests\Api\V1\Batch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un parapheur (batch) — D06.
 *
 * Règles reprises de `BatchController::update()` (relu le 2026-08-04), avec l'unicité
 * du code ignorée sur l'enregistrement courant.
 */
class UpdateBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'nullable', 'max:10', Rule::unique('batches', 'code')->ignore($this->route('batch'))],
            'name' => 'sometimes|required|max:100',
        ];
    }
}
