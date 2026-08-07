<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordPeriodicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'type' => [
                'id' => $this->type?->id,
                'code' => $this->type?->code,
                'name' => $this->type?->name,
            ],
            'creator' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ],
            'organisation' => [
                'id' => $this->organisation?->id,
                'name' => $this->organisation?->name,
            ],
            'publication_info' => [
                'issn' => $this->issn,
                'publisher' => $this->publisher,
                'frequency' => $this->frequency,
                'start_year' => $this->start_year,
                'end_year' => $this->end_year,
            ],
            'classification' => [
                'subjects' => $this->subjects,
                'language' => $this->language,
                'country_of_publication' => $this->country_of_publication,
            ],
            'statistics' => [
                'total_issues' => $this->issues()->count(),
                'total_articles' => $this->articles()->count(),
                'active_subscriptions' => $this->subscriptions()->where('status', 'active')->count(),
            ],
            'metadata' => $this->metadata,
            'status' => $this->status,
            'dates' => [
                'created_at' => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ],
        ];
    }
}
