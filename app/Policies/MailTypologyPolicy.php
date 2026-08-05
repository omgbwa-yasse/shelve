<?php

namespace App\Policies;

use App\Models\MailTypology;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class MailTypologyPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'mail_typology_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, MailTypology $mailTypology): bool|Response
    {
        return $this->canView($user, $mailTypology, 'mail_typology_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'mail_typology_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, MailTypology $mailTypology): bool|Response
    {
        return $this->canUpdate($user, $mailTypology, 'mail_typology_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, MailTypology $mailTypology): bool|Response
    {
        return $this->canDelete($user, $mailTypology, 'mail_typology_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, MailTypology $mailTypology): bool|Response
    {
        return $this->canForceDelete($user, $mailTypology, 'mail_typology_force_delete');
    }
}
