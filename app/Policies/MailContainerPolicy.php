<?php

namespace App\Policies;

use App\Models\MailContainer;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class MailContainerPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'mail_container_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, MailContainer $mailContainer): bool|Response
    {
        return $this->canView($user, $mailContainer, 'mail_container_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'mail_container_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, MailContainer $mailContainer): bool|Response
    {
        return $this->canUpdate($user, $mailContainer, 'mail_container_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, MailContainer $mailContainer): bool|Response
    {
        return $this->canDelete($user, $mailContainer, 'mail_container_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, MailContainer $mailContainer): bool|Response
    {
        return $this->canForceDelete($user, $mailContainer, 'mail_container_force_delete');
    }
}
