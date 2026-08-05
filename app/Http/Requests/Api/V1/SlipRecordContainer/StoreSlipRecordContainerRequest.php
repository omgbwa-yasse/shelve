<?php

namespace App\Http\Requests\Api\V1\SlipRecordContainer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une association document↔contenant — D04.
 *
 * Règles reprises de `SlipRecordContainerController::store()` (relu le 2026-08-04).
 * `slip_record_id` vient de la route et `creator_id` de l'agent authentifié.
 * (Le Blade validait `slip_record_id` contre `slips` — corrigé sur `slip_records`.)
 */
class StoreSlipRecordContainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'container_id' => 'required|exists:containers,id',
            'description' => 'required|string|max:200',
        ];
    }
}
