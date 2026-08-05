<?php

namespace App\Http\Requests\Api\V1\MailArchive;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Création d'une archive de courrier — D06.
 *
 * Règles reprises de `MailArchiveController::update()` et du motif `addMails()`
 * (relus le 2026-08-04). L'archivage en masse (plusieurs courriers + bascule
 * `is_archived`) reste en TODO dans le contrôleur. `archived_by` est posé depuis
 * l'agent authentifié, jamais accepté du client.
 */
class StoreMailArchiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        // L'autorisation est portée par la Policy, appelée depuis le contrôleur.
        return true;
    }

    public function rules(): array
    {
        return [
            'container_id' => 'required|exists:mail_containers,id',
            'mail_id' => 'required|exists:mails,id',
            'document_type' => 'required|in:original,duplicate,copy',
        ];
    }
}
