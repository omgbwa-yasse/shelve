<?php

namespace App\Http\Requests\Api\V1\Batch;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un parapheur (batch) — D06.
 *
 * Règles reprises de `BatchController::store()` et `BatchHandlerController::create()`
 * (relues le 2026-08-04). `organisation_holder_id` est posé depuis l'agent authentifié
 * dans le contrôleur, jamais accepté du client.
 */
class StoreBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'nullable|unique:batches|max:10',
            'name' => 'required|max:100',
        ];
    }
}
