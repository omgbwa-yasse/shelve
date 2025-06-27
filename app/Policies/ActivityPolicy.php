<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class ActivityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('activity_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Activity $activity): bool
    {
        return $user->hasPermissionTo('activity_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $activity);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('activity_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Activity $activity): bool
    {
        return $user->hasPermissionTo('activity_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $activity);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Activity $activity): bool
    {
        return $user->hasPermissionTo('activity_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $activity);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Activity $activity): bool
    {
        return $user->hasPermissionTo('activity_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $activity);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Activity $activity): bool
    {
        $cacheKey = "activity_org_access:{$user->id}:{$activity->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $activity) {
            // For models directly linked to organisations
            if (method_exists($activity, 'organisations')) {
                foreach($activity->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($activity->organisation_id)) {
                return $activity->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($activity, 'activity') && $activity->activity) {
                foreach($activity->activity->organisations as $organisation) {
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
