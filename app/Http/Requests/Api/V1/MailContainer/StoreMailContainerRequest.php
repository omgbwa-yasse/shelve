<?php

namespace App\Http\Requests\Api\V1\MailContainer;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un contenant de courrier — D06.
 *
 * Règles reprises de `MailContainerController::store()` (relu le 2026-08-04).
 * `created_by` et `creator_organisation_id` sont posés depuis l'agent authentifié
 * dans le contrôleur, jamais acceptés du client.
 */
class StoreMailContainerRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|unique:mail_containers|max:50',
            'name' => 'required|max:100',
            'property_id' => 'required|exists:container_properties,id',
        ];
    }
}
