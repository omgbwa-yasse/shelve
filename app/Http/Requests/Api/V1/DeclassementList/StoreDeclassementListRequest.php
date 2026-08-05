<?php

namespace App\Http\Requests\Api\V1\DeclassementList;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une liste de déclassement — D07.
 *
 * Règles reprises de `DeclassementListController::store()` (relu le 2026-08-04).
 * `record_ids.*` cible la table unifiée `records` (le Blade visait `record_physicals`).
 * Champs gérés serveur retirés : `organisation_id`, `declassement_status_id`,
 * `query_criteria`, `creator_id`, indicateurs d'approbation/validation/traitement.
 */
class StoreDeclassementListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|max:20|unique:declassement_lists,code',
            'name' => 'required|max:200',
            'description' => 'nullable',
            'record_ids' => 'nullable|array',
            'record_ids.*' => 'exists:records,id',
            'generate_from_query' => 'nullable|boolean',
            'activity_id' => 'nullable|exists:activities,id',
        ];
    }
}
