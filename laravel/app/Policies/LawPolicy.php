<?php

namespace App\Policies;

use App\Models\Law;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class LawPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'law_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Law $law): bool|Response
    {
        return $this->canView($user, $law, 'law_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'law_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Law $law): bool|Response
    {
        return $this->canUpdate($user, $law, 'law_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Law $law): bool|Response
    {
        return $this->canDelete($user, $law, 'law_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Law $law): bool|Response
    {
        return $this->canForceDelete($user, $law, 'law_force_delete');
    }

    /**
     */
}
