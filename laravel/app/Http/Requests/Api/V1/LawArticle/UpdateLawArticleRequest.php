<?php

namespace App\Http\Requests\Api\V1\LawArticle;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Modification d'un article de loi — D01.
 *
 * Module jamais branché côté Blade : pas de comportement d'origine à reprendre.
 * Règles dérivées du schéma, complétées le 2026-08-04 (contrainte `exists` sur
 * `law_id`).
 */
class UpdateLawArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:10'],
            'name' => ['sometimes', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string'],
            'law_id' => ['sometimes', 'integer', 'exists:laws,id'],
        ];
    }
}
