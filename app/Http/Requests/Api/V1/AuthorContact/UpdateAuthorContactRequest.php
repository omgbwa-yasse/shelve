<?php

namespace App\Http\Requests\Api\V1\AuthorContact;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un contact d'auteur — D01.
 */
class UpdateAuthorContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author_id' => 'sometimes|exists:authors,id',
            'phone1' => 'nullable|string|max:191',
            'phone2' => 'nullable|string|max:191',
            'email' => 'nullable|string|max:191',
            'address' => 'nullable|string|max:191',
            'website' => 'nullable|string|max:191',
            'fax' => 'nullable|string|max:191',
            'other' => 'nullable|string',
            'po_box' => 'nullable|string|max:191',
        ];
    }
}
