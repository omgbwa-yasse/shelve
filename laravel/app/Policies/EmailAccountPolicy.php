<?php

namespace App\Policies;

use App\Models\EmailAccount;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Accès aux comptes de messagerie (identifiants IMAP/SMTP) — réservé aux
 * utilisateurs de l'organisation propriétaire du compte.
 */
class EmailAccountPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'email_account_viewAny');
    }

    public function view(?User $user, EmailAccount $emailAccount): bool|Response
    {
        return $this->canView($user, $emailAccount, 'email_account_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'email_account_create');
    }

    public function update(?User $user, EmailAccount $emailAccount): bool|Response
    {
        return $this->canUpdate($user, $emailAccount, 'email_account_update');
    }

    public function delete(?User $user, EmailAccount $emailAccount): bool|Response
    {
        return $this->canDelete($user, $emailAccount, 'email_account_delete');
    }

    public function forceDelete(?User $user, EmailAccount $emailAccount): bool|Response
    {
        return $this->canForceDelete($user, $emailAccount, 'email_account_force_delete');
    }
}
