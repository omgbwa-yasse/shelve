<?php

namespace App\Policies;

use App\Models\MailAction;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class MailActionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'mail_action_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, MailAction $mailAction): bool|Response
    {
        return $this->canView($user, $mailAction, 'mail_action_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'mail_action_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, MailAction $mailAction): bool|Response
    {
        return $this->canUpdate($user, $mailAction, 'mail_action_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, MailAction $mailAction): bool|Response
    {
        return $this->canDelete($user, $mailAction, 'mail_action_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, MailAction $mailAction): bool|Response
    {
        return $this->canForceDelete($user, $mailAction, 'mail_action_force_delete');
    }
}
