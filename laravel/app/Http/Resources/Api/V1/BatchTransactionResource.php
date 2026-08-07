<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D06 — transaction de parapheur, relue le 2026-08-04 contre `BatchReceivedController`,
 * `BatchSendController` et le schéma.
 */
class BatchTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'batch_id' => $this->batch_id,
            'organisation_send_id' => $this->organisation_send_id,
            'organisation_received_id' => $this->organisation_received_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
