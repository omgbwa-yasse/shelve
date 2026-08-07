<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * D06 — courrier, relu le 2026-08-04 contre `MailController` et le schéma.
 *
 * Champs calculés du modèle : `is_overdue`, `days_until_deadline`, `processing_time_minutes`,
 * `involves_current_org`. Le statut est exposé en chaîne brute (`draft`, `in_progress`…) :
 * les conventions de l'API v1 n'exposent pas les enums Laravel, le consommateur lit les
 * valeurs du schéma (`mails.status`).
 */
class MailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'date' => $this->date?->toIso8601ZuluString(),
            'description' => $this->description,
            'document_type' => $this->document_type,
            'status' => $this->status?->value ?? $this->status,
            'mail_type' => $this->mail_type,
            'priority_id' => $this->priority_id,
            'typology_id' => $this->typology_id,
            'action_id' => $this->action_id,
            'sender_user_id' => $this->sender_user_id,
            'sender_organisation_id' => $this->sender_organisation_id,
            'sender_type' => $this->sender_type,
            'external_sender_id' => $this->external_sender_id,
            'external_sender_organization_id' => $this->external_sender_organization_id,
            'recipient_user_id' => $this->recipient_user_id,
            'recipient_organisation_id' => $this->recipient_organisation_id,
            'recipient_type' => $this->recipient_type,
            'external_recipient_id' => $this->external_recipient_id,
            'external_recipient_organization_id' => $this->external_recipient_organization_id,
            'assigned_to' => $this->assigned_to,
            'assigned_organisation_id' => $this->assigned_organisation_id,
            'assigned_at' => $this->assigned_at?->toIso8601ZuluString(),
            'is_archived' => (bool) $this->is_archived,
            'deadline' => $this->deadline?->toIso8601ZuluString(),
            'is_overdue' => $this->isOverdue(),
            'days_until_deadline' => $this->getDaysUntilDeadlineAttribute(),
            'processed_at' => $this->processed_at?->toIso8601ZuluString(),
            'processing_time_minutes' => $this->getProcessingTimeAttribute(),
            'estimated_processing_time' => $this->estimated_processing_time,
            'delivery_method' => $this->delivery_method,
            'tracking_number' => $this->tracking_number,
            'sent_at' => $this->sent_at?->toIso8601ZuluString(),
            'received_at' => $this->received_at?->toIso8601ZuluString(),
            'receipt_confirmed' => (bool) $this->receipt_confirmed,
            'involves_current_org' => $this->getInvolvesCurrentOrgAttribute(),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
