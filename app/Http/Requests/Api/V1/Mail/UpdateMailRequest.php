<?php

namespace App\Http\Requests\Api\V1\Mail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Modification d'un courrier — D06.
 *
 * Règles reprises des formulaires `updateIncoming` / `updateOutgoing` de `MailController`
 * (relues le 2026-08-04). Divergences assumées :
 *   - les champs d'organisation (`sender_organisation_id`, `recipient_organisation_id`,
 *     `assigned_organisation_id`) ne sont PAS acceptés du client : ils sont gérés côté
 *     serveur (isolation R03) ;
 *   - `attachments` (téléversement) n'est pas porté (sous-ressource `mail-attachments` en TODO).
 */
class UpdateMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', 'max:30', Rule::unique('mails', 'code')->ignore($this->route('mail'))],
            'name' => 'sometimes|required|max:150',
            'date' => 'sometimes|required|date',
            'description' => 'nullable',
            'document_type' => 'sometimes|required|in:original,duplicate,copy',
            'status' => ['sometimes', 'required', Rule::in([
                'draft', 'pending_review', 'in_progress', 'pending_approval', 'approved',
                'transmitted', 'completed', 'rejected', 'cancelled', 'overdue',
            ])],
            'mail_type' => 'sometimes|required|in:internal,incoming,outgoing',
            'typology_id' => 'sometimes|required|exists:mail_typologies,id',
            'priority_id' => 'sometimes|nullable|exists:mail_priorities,id',
            'action_id' => 'sometimes|nullable|exists:mail_actions,id',
            'deadline' => 'sometimes|nullable|date|after:today',
            'estimated_processing_time' => 'sometimes|nullable|integer|min:1',
            'delivery_method' => 'sometimes|nullable|string|max:191',
            'tracking_number' => 'sometimes|nullable|string|max:191',
            'sender_user_id' => 'sometimes|nullable|exists:users,id',
            'recipient_user_id' => 'sometimes|nullable|exists:users,id',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'external_sender_id' => 'sometimes|nullable|exists:external_contacts,id',
            'external_sender_organization_id' => 'sometimes|nullable|exists:external_organizations,id',
            'external_recipient_id' => 'sometimes|nullable|exists:external_contacts,id',
            'external_recipient_organization_id' => 'sometimes|nullable|exists:external_organizations,id',
        ];
    }
}
