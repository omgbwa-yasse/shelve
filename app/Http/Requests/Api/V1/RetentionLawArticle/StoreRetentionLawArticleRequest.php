<?php

namespace App\Http\Requests\Api\V1\RetentionLawArticle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ajout d'une exigence réglementaire à une durée de conservation — D07.
 *
 * Le contrôleur Blade est vide ; les règles sont déduites du schéma
 * (`retention_law_articles`) et du modèle. Unicité de la paire garantie en base
 * (clé composite) et par `firstOrCreate`.
 */
class StoreRetentionLawArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'retention_id' => [
                'required',
                'integer',
                'exists:retentions,id',
                Rule::unique('retention_law_articles')->where(fn ($q) => $q->where('law_article_id', $this->input('law_article_id'))),
            ],
            'law_article_id' => ['required', 'integer', 'exists:law_articles,id'],
        ];
    }
}
