<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D07 — exigence réglementaire (pivot `retention_law_articles`).
 */
class RetentionLawArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'retention_id' => $this->retention_id,
            'law_article_id' => $this->law_article_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
