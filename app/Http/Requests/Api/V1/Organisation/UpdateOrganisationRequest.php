<?php

namespace App\Http\Requests\Api\V1\Organisation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une organisation — D09.
 *
 * Règles reprises de `OrganisationController::update()` (relu le 2026-08-05),
 * passées en `sometimes` (PATCH). Un garde-fou est ajouté : `parent_id` ne peut
 * pas désigner l'organisation elle-même (cycle de hiérarchie interdit).
 */
class UpdateOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $self = $this->route('organisation');

        return [
            'code' => ['sometimes', 'required', 'string', 'max:10', Rule::unique('organisations', 'code')->ignore($self->id)],
            'name' => ['sometimes', 'required', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string'],
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:organisations,id',
                Rule::notIn([$self->id]),
            ],
        ];
    }
}
