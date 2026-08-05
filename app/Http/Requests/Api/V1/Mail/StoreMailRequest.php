<?php

namespace App\Http\Requests\Api\V1\Mail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Création d'un courrier — D06.
 *
 * Règles reprises des formulaires `storeIncoming` / `storeOutgoing` de `MailController`
 * (relues le 2026-08-04), fusionnés en un seul point d'entrée : `mail_type` (incoming /
 * outgoing) décide du rôle de l'organisation courante et des champs expéditeur/destinataire
 * attendus. Divergences assumées :
 *   - `mail_type` est un champ explicite (les deux routes Blade divergent) ;
 *   - `attachments` (téléversement) n'est pas porté ici — sous-ressource `mail-attachments`
 *     en TODO (E2, phase 3) ;
 *   - les champs d'organisation (`recipient_organisation_id`, `sender_organisation_id`)
 *     d'un courrier qui engage l'organisation courante ne sont PAS acceptés du client :
 *     ils sont posés côté serveur (isolation R03) — sauf l'expéditeur/destinataire externe
 *     ou tiers (`sender_organisation_id` entrant) qui reste accepté comme en Blade.
 */
class StoreMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        $mailType = $this->input('mail_type', 'incoming');

        return [
            'code' => 'nullable|string|max:30',
            'mail_type' => 'sometimes|required|in:incoming,outgoing',
            'name' => 'required|max:150',
            'date' => 'required|date',
            'description' => 'nullable',
            'document_type' => 'required|in:original,duplicate,copy',
            'typology_id' => 'required|exists:mail_typologies,id',
            'priority_id' => 'nullable|exists:mail_priorities,id',
            'action_id' => 'nullable|exists:mail_actions,id',
            'deadline' => 'nullable|date|after:today',
            'estimated_processing_time' => 'nullable|integer|min:1',
            'delivery_method' => 'nullable|string|max:191',
            'tracking_number' => 'nullable|string|max:191',

            // Expéditeur (courrier entrant) — l'organisation courante est le récepteur.
            'sender_type' => [Rule::requiredIf($mailType === 'incoming'), 'in:external_contact,external_organization,organisation'],
            'external_sender_id' => 'nullable|exists:external_contacts,id',
            'external_sender_organization_id' => 'nullable|exists:external_organizations,id',
            'sender_organisation_id' => 'nullable|exists:organisations,id',

            // Destinataire (courrier sortant) — l'organisation courante est l'émetteur.
            'recipient_type' => [Rule::requiredIf($mailType === 'outgoing'), 'in:external_contact,external_organization'],
            'external_recipient_id' => 'nullable|exists:external_contacts,id',
            'external_recipient_organization_id' => 'nullable|exists:external_organizations,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $mailType = $this->input('mail_type', 'incoming');

            if ($mailType === 'incoming') {
                $this->requireSenderFields($validator);
            } else {
                $this->requireRecipientFields($validator);
            }
        });
    }

    private function requireSenderFields($validator): void
    {
        $senderType = $this->input('sender_type');

        if ($senderType === 'external_contact' && $this->isBlankInput('external_sender_id')) {
            $validator->errors()->add('external_sender_id', 'Veuillez sélectionner un contact externe.');
        }

        if ($senderType === 'external_organization' && $this->isBlankInput('external_sender_organization_id')) {
            $validator->errors()->add('external_sender_organization_id', 'Veuillez sélectionner une organisation externe.');
        }

        if ($senderType === 'organisation' && $this->isBlankInput('sender_organisation_id')) {
            $validator->errors()->add('sender_organisation_id', 'Veuillez sélectionner une organisation.');
        }
    }

    private function requireRecipientFields($validator): void
    {
        $recipientType = $this->input('recipient_type');

        if ($recipientType === 'external_contact' && $this->isBlankInput('external_recipient_id')) {
            $validator->errors()->add('external_recipient_id', 'Veuillez sélectionner un contact externe.');
        }

        if ($recipientType === 'external_organization' && $this->isBlankInput('external_recipient_organization_id')) {
            $validator->errors()->add('external_recipient_organization_id', 'Veuillez sélectionner une organisation externe.');
        }
    }

    private function isBlankInput(string $key): bool
    {
        return empty($this->input($key));
    }
}

