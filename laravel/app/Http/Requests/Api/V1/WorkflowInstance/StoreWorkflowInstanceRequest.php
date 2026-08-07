<?php

namespace App\Http\Requests\Api\V1\WorkflowInstance;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une instance de workflow — D13.
 *
 * Règles reprises de `WorkflowInstanceController::store()` (relu le 2026-08-04).
 * Champs gérés serveur retirés : `organisation_id`, `status` (démarrage en
 * `running`), `current_state`, `started_by`.
 */
class StoreWorkflowInstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'definition_id' => 'required|exists:workflow_definitions,id',
            'name' => 'required|string|max:190',
        ];
    }
}
