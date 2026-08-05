<?php

namespace App\Http\Resources\Api\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D15 — news du portail public. Dates ISO-8601, booléens réels, aucun secret
 * (l'auteur est exposé en id + nom uniquement).
 */
class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'content' => $this->content,
            'featured' => (bool) $this->featured,
            'published_at' => $this->published_at?->toIso8601String(),
            'image_path' => $this->image_path,
            'image_url' => $this->when($this->image_path, fn () => str_starts_with($this->image_path, 'http')
                ? $this->image_path
                : url($this->image_path)),
            'author' => $this->whenLoaded('author', fn () => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
