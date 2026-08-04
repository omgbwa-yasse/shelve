<?php

namespace App\Policies;

use App\Models\Mail;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class MailPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Supports guest users with optional type-hint.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'mail_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     * Supports guest users with optional type-hint.
     */
    public function view(?User $user, Mail $mail): bool|Response
    {
        // Le cloisonnement organisationnel ne reconnaît que l'entité du courrier et
        // ses ancêtres. Or le workflow désigne nommément des acteurs qui peuvent
        // appartenir à un service rattaché : un validateur N+1 placé dans un service
        // enfant se voyait ainsi refuser l'accès au courrier qu'il devait viser.
        // Les personnes explicitement engagées dans le circuit y ont droit.
        if ($user && $this->hasPermission($user, 'mail_view') && $this->isDesignatedActor($user, $mail)) {
            return true;
        }

        return $this->canView($user, $mail, 'mail_view');
    }

    /**
     * L'utilisateur est-il nommément engagé dans le circuit de ce courrier ?
     */
    private function isDesignatedActor(User $user, Mail $mail): bool
    {
        $acteurs = array_map('intval', array_filter([
            $mail->assigned_to,        // validateur courant ou agent affecté
            $mail->sender_user_id,     // initiateur
            $mail->recipient_user_id,  // destinataire nommé
            $mail->dg_signed_by,       // signataire
        ]));

        if (in_array((int) $user->id, $acteurs, true)) {
            return true;
        }

        // Direction cotée dont l'utilisateur a la charge (y compris par intérim).
        return $mail->exists && $mail->isHandledBy($user);
    }

    /**
     * Determine whether the user can create models.
     * Supports guest users with optional type-hint.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'mail_create');
    }

    /**
     * Determine whether the user can update the model.
     * Supports guest users with optional type-hint.
     */
    public function update(?User $user, Mail $mail): bool|Response
    {
        return $this->canUpdate($user, $mail, 'mail_update');
    }

    /**
     * Determine whether the user can delete the model.
     * Supports guest users with optional type-hint.
     */
    public function delete(?User $user, Mail $mail): bool|Response
    {
        return $this->canDelete($user, $mail, 'mail_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Supports guest users with optional type-hint.
     */
    public function forceDelete(?User $user, Mail $mail): bool|Response
    {
        return $this->canForceDelete($user, $mail, 'mail_force_delete');
    }
}
