<?php

namespace App\Http\Requests\Api\V1\AuthorContact;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un contact d'auteur — D01.
 *
 * Le contrôleur Blade ne validait rien (`create($request->all())`) : règles
 * reconstituées du schéma (risque R01).
 */
class StoreAuthorContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author_id' => 'required|exists:authors,id',
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
