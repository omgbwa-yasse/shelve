<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * D10 — transaction de courrier (résultat de la recherche feedback), porté le 2026-08-05
 * contre `SearchMailFeedbackController` et `MailTransaction`.
 */
class MailTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'date_creation' => $this->date_creation
                ? Carbon::parse($this->date_creation)->toIso8601ZuluString()
                : null,
            'mail_id' => $this->mail_id,
            'user_send_id' => $this->user_send_id,
            'organisation_send_id' => $this->organisation_send_id,
            'user_received_id' => $this->user_received_id,
            'organisation_received_id' => $this->organisation_received_id,
            'action_id' => $this->action_id,
            'to_return' => (bool) $this->to_return,
            'batch_id' => $this->batch_id,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
