<?php

namespace App\Http\Requests\Api\V1\WorkflowDefinition;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une définition de workflow — D13.
 *
 * Règles reprises de `WorkflowDefinitionController::update()` (relu le 2026-08-04),
 * passées en `sometimes` pour la mise à jour partielle (PATCH). `updated_by` est
 * posé depuis l'agent dans le contrôleur.
 */
class UpdateWorkflowDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'bpmn_xml' => 'sometimes|string',
            'status' => 'sometimes|string|in:draft,active,archived',
        ];
    }
}
