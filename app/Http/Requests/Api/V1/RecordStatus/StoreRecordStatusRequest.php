<?php

namespace App\Http\Requests\Api\V1\RecordStatus;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un statut de notice — D02.
 *
 * Règles reprises de `RecordStatusController::store()` (relu le 2026-08-04).
 * Note : le contrôleur Blade valide `unique:record_status` mais la table réelle
 * est `record_statuses` — la règle est alignée sur le schéma.
 */
class StoreRecordStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:100|unique:record_statuses,name',
            'description' => 'nullable',
        ];
    }
}
