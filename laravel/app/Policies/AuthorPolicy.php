<?php

namespace App\Policies;

use App\Models\Author;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class AuthorPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'author_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Author $author): bool|Response
    {
        return $this->canView($user, $author, 'author_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'author_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Author $author): bool|Response
    {
        return $this->canUpdate($user, $author, 'author_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Author $author): bool|Response
    {
        return $this->canDelete($user, $author, 'author_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Author $author): bool|Response
    {
        return $this->canForceDelete($user, $author, 'author_force_delete');
    }
}
