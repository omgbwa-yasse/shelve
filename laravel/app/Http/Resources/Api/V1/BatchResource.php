<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D06 — parapheur (batch), relu le 2026-08-04 contre `BatchController` et le schéma.
 *
 * `mails_count` est posé par `withCount('mails')` dans le contrôleur (l'index Blade
 * `BatchHandlerController::list()` l'utilise également).
 */
class BatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'organisation_holder_id' => $this->organisation_holder_id,
            'mails_count' => $this->mails_count ?? $this->mails()->count(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
