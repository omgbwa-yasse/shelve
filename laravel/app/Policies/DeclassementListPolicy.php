<?php

namespace App\Policies;

use App\Models\DeclassementList;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class DeclassementListPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'declassement_list_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, DeclassementList $declassementList): bool|Response
    {
        return $this->canView($user, $declassementList, 'declassement_list_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'declassement_list_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, DeclassementList $declassementList): bool|Response
    {
        return $this->canUpdate($user, $declassementList, 'declassement_list_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, DeclassementList $declassementList): bool|Response
    {
        return $this->canDelete($user, $declassementList, 'declassement_list_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, DeclassementList $declassementList): bool|Response
    {
        return $this->canForceDelete($user, $declassementList, 'declassement_list_force_delete');
    }
}
