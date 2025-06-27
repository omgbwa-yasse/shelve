<?php

namespace App\Policies;

use App\Models\Container;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class ContainerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('container_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Container $container): bool
    {
        return $user->hasPermissionTo('container_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $container);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('container_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Container $container): bool
    {
        return $user->hasPermissionTo('container_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $container);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Container $container): bool
    {
        return $user->hasPermissionTo('container_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $container);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Container $container): bool
    {
        return $user->hasPermissionTo('container_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $container);
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
