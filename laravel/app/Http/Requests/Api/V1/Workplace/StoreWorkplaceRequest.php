<?php

namespace App\Http\Requests\Api\V1\Workplace;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un espace de travail — D12.
 *
 * Règles reprises de `WorkplaceController::store()` (relu le 2026-08-04).
 * `code` est saisi par l'utilisateur à la création : il sert de slug d'accès
 * (`/workplace/{code}`) et est normalisé en minuscules côté serveur.
 * `status`, `organisation_id`, `owner_id`, `created_by` et les compteurs sont
 * posés côté serveur, jamais acceptés du client.
 */
class StoreWorkplaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/i',
                'unique:workplaces,code',
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:workplace_categories,id',
            'template_id' => 'nullable|exists:workplace_templates,id',
            'is_public' => 'boolean',
            'allow_external_sharing' => 'boolean',
            'max_members' => 'nullable|integer|min:1',
            'max_storage_mb' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code est requis — il servira d\'adresse de l\'espace (ex. `rh`, `sia2019`, `dg-sg`).',
            'code.regex' => 'Le code ne doit contenir que des lettres, chiffres et tirets (ex. `dg-sg`).',
            'code.unique' => 'Ce code est déjà utilisé par un autre espace de travail.',
        ];
    }
}
