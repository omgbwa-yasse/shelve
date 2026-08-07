<?php

namespace App\Policies;

use App\Models\MailPriority;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class MailPriorityPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'mail_priority_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, MailPriority $mailPriority): bool|Response
    {
        return $this->canView($user, $mailPriority, 'mail_priority_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'mail_priority_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, MailPriority $mailPriority): bool|Response
    {
        return $this->canUpdate($user, $mailPriority, 'mail_priority_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, MailPriority $mailPriority): bool|Response
    {
        return $this->canDelete($user, $mailPriority, 'mail_priority_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, MailPriority $mailPriority): bool|Response
    {
        return $this->canForceDelete($user, $mailPriority, 'mail_priority_force_delete');
    }
}
