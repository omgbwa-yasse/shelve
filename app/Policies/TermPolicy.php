<?php

namespace App\Policies;

use App\Models\Term;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class TermPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'term_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Term $term): bool|Response
    {
        return $this->canView($user, $term, 'term_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'term_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Term $term): bool|Response
    {
        return $this->canUpdate($user, $term, 'term_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Term $term): bool|Response
    {
        return $this->canDelete($user, $term, 'term_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Term $term): bool|Response
    {
        return $this->canForceDelete($user, $term, 'term_force_delete');
    }
}
