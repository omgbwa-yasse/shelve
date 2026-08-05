<?php

namespace App\Http\Requests\Api\V1\Author;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un auteur — D01.
 *
 * Règles reprises de `AuthorController::store()` et `storeApi()` (relu le 2026-08-04).
 */
class StoreAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_id' => 'required|exists:author_types,id',
            'name' => 'required|string|max:255',
            'parallel_name' => 'nullable|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'lifespan' => 'nullable|string|max:255',
            'locations' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:authors,id',
        ];
    }
}
