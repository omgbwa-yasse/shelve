<?php

namespace App\Policies;

use App\Models\EmailMessage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Accès aux messages — l'organisation est dérivée du compte de messagerie
 * propriétaire (`EmailMessage::getOrganisationIdAttribute()`), pas d'une
 * colonne propre à la table.
 */
class EmailMessagePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'email_message_viewAny');
    }

    public function view(?User $user, EmailMessage $emailMessage): bool|Response
    {
        return $this->canView($user, $emailMessage, 'email_message_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'email_message_create');
    }

    public function update(?User $user, EmailMessage $emailMessage): bool|Response
    {
        return $this->canUpdate($user, $emailMessage, 'email_message_update');
    }

    public function delete(?User $user, EmailMessage $emailMessage): bool|Response
    {
        return $this->canDelete($user, $emailMessage, 'email_message_delete');
    }
}
