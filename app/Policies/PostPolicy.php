<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class PostPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'post_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool|Response
    {
        return $this->canView($user, $post, 'post_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'post_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool|Response
    {
        return $this->canUpdate($user, $post, 'post_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool|Response
    {
        return $this->canDelete($user, $post, 'post_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool|Response
    {
        return $this->canForceDelete($user, $post, 'post_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Post $post): bool
    {
        $cacheKey = "post_org_access:{$user->id}:{$post->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $post) {
            // For models directly linked to organisations
            if (method_exists($post, 'organisations')) {
                foreach($post->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($post->organisation_id)) {
                return $post->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($post, 'activity') && $post->activity) {
                foreach($post->activity->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // Default: allow access if no specific organisation restriction
            return true;
        });
    }
}
