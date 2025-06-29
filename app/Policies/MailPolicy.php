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
        return $this->canView($user, $mail, 'mail_view');
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
