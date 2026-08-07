<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailTagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'name' => $this->name,
            'color' => $this->color,
            'messages_count' => $this->whenCounted('messages'),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
        ];
    }
}
