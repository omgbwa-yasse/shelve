<?php

namespace App\Http\Requests\Api\V1\ExternalOrganization;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'une organisation externe — D01.
 *
 * Règles reprises de `ExternalOrganizationController::update()` (relu le 2026-08-04).
 */
class UpdateExternalOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'legal_form' => 'nullable|string|max:100',
            'registration_number' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'is_verified' => 'boolean',
            'notes' => 'nullable|string',
        ];
    }
}
