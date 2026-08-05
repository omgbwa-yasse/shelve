<?php

namespace App\Http\Requests\Api\V1\AiTemplate;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mise à jour d'un modèle de document IA — D14.
 *
 * Seules les métadonnées sont modifiables : le fichier (et donc `file_name`,
 * `file_path`, `mime_type`, `size`) reste géré serveur.
 */
class UpdateAiTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|nullable|string|max:200',
            'category' => 'sometimes|nullable|string|max:100',
            'description' => 'sometimes|nullable|string',
        ];
    }
}
