<?php

namespace App\Http\Requests\Api\V1\WorkflowDefinition;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une définition de workflow — D13.
 *
 * Règles reprises de `WorkflowDefinitionController::store()` (relu le 2026-08-04).
 * Champs gérés serveur retirés : `organisation_id`, `version` (incrémentée par nom),
 * `created_by`, `updated_by`.
 */
class StoreWorkflowDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'bpmn_xml' => 'required|string',
            'status' => 'required|string|in:draft,active,archived',
        ];
    }
}
