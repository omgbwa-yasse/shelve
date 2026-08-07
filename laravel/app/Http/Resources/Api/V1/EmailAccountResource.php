<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ne renvoie jamais imap_password/smtp_password (déjà $hidden côté modèle, redondant ici par sûreté). */
class EmailAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organisation_id' => $this->organisation_id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'email_address' => $this->email_address,
            'imap_host' => $this->imap_host,
            'imap_port' => $this->imap_port,
            'imap_encryption' => $this->imap_encryption,
            'imap_username' => $this->imap_username,
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_encryption' => $this->smtp_encryption,
            'smtp_username' => $this->smtp_username,
            'default_from_name' => $this->default_from_name,
            'is_active' => (bool) $this->is_active,
            'last_synced_at' => $this->last_synced_at?->toIso8601ZuluString(),
            'last_sync_error' => $this->last_sync_error,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
