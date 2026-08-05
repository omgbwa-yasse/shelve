<?php

namespace App\Http\Requests\Api\V1\RetentionLawArticle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'une exigence réglementaire — D07.
 *
 * `sometimes` pour la mise à jour partielle ; l'unicité de la paire ignore la paire
 * courante portée par la route {retention}/{lawArticle}.
 */
class UpdateRetentionLawArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'retention_id' => [
                'sometimes',
                'integer',
                'exists:retentions,id',
                Rule::unique('retention_law_articles')
                    ->ignore($this->route('retention'))
                    ->where(fn ($q) => $q->where('law_article_id', $this->input('law_article_id', $this->route('lawArticle')))),
            ],
            'law_article_id' => ['sometimes', 'integer', 'exists:law_articles,id'],
        ];
    }
}
