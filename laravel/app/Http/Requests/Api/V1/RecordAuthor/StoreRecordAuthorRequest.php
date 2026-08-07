<?php

namespace App\Http\Requests\Api\V1\RecordAuthor;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Association notice ↔ auteur — D02.
 *
 * `record_id` est porté par la notice parente de la route, jamais accepté du client.
 * L'auteur est un référentiel D01 global : seule son existence est vérifiée.
 */
class StoreRecordAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author_id' => 'required|exists:authors,id',
        ];
    }
}
