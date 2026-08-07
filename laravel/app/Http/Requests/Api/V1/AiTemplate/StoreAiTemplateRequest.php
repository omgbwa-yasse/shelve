<?php

namespace App\Http\Requests\Api\V1\AiTemplate;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un modèle de document IA — D14.
 *
 * Règles reprises de `AiTemplateController::store()` (upload `template_file`).
 * `file_name`, `file_path`, `mime_type`, `size` et `created_by` sont gérés serveur.
 */
class StoreAiTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_file' => 'required|file',
            'name' => 'nullable|string|max:200',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ];
    }
}
