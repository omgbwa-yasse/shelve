<?php

namespace App\Http\Requests\Api\V1\Task;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une tâche — D12.
 *
 * Règles reprises de `TaskController::store()` (relu le 2026-08-04).
 * `created_by` est posé côté serveur ; `organisation_id` n'est pas porté (le
 * modèle ne l'utilise pas — les tâches sont un référentiel global).
 */
class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:190',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ];
    }
}
