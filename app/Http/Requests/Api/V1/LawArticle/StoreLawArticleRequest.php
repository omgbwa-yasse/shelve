<?php

namespace App\Http\Requests\Api\V1\LawArticle;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'un article de loi — D01.
 *
 * `LawArticleController` (Blade) était vide, sans vue : module jamais branché.
 * Règles dérivées du schéma, complétées le 2026-08-04 (contrainte `exists` sur
 * `law_id`, absente du fallback généré).
 */
class StoreLawArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'law_id' => ['required', 'integer', 'exists:laws,id'],
        ];
    }
}
