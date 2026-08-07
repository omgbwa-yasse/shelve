<?php

namespace App\Http\Requests\Api\V1\ExternalContact;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un contact externe — D01.
 *
 * Règles reprises de `ExternalContactController::store()` (relu le 2026-08-04).
 */
class StoreExternalContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'position' => 'nullable|string|max:255',
            'external_organization_id' => 'nullable|exists:external_organizations,id',
            'is_primary_contact' => 'boolean',
            'is_verified' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }
}
