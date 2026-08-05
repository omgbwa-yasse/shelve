<?php

namespace App\Http\Requests\Api\V1\WorkflowInstance;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une instance de workflow — D13.
 *
 * Non exposée en API (le Blade ne propose pas d'`update` : l'évolution passe par les
 * actions start/pause/resume/cancel). Conservée pour cohérence du jeu de fichiers.
 */
class UpdateWorkflowInstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:190',
        ];
    }
}
