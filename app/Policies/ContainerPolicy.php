<?php

namespace App\Policies;

use App\Models\Container;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class ContainerPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'container_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Container $container): bool|Response
    {
        return $this->canView($user, $container, 'container_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'container_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Container $container): bool|Response
    {
        return $this->canUpdate($user, $container, 'container_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Container $container): bool|Response
    {
        return $this->canDelete($user, $container, 'container_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Container $container): bool|Response
    {
        return $this->canForceDelete($user, $container, 'container_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Container $container): bool
    {
        $cacheKey = "container_org_access:{$user->id}:{$container->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $container) {
            // For models directly linked to organisations
            if (method_exists($container, 'organisations')) {
                foreach($container->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($container->organisation_id)) {
                return $container->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($container, 'activity') && $container->activity) {
                foreach($container->activity->organisations as $organisation) {
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
