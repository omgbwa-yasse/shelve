<?php

namespace App\Policies;

use App\Models\Language;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class LanguagePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'language_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Language $language): bool|Response
    {
        return $this->canView($user, $language, 'language_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'language_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Language $language): bool|Response
    {
        return $this->canUpdate($user, $language, 'language_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Language $language): bool|Response
    {
        return $this->canDelete($user, $language, 'language_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Language $language): bool|Response
    {
        return $this->canForceDelete($user, $language, 'language_force_delete');
    }

    /**
     */
}
