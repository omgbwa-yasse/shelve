<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class ActivityPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'activity_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Activity $activity): bool|Response
    {
        return $this->canView($user, $activity, 'activity_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'activity_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Activity $activity): bool|Response
    {
        return $this->canUpdate($user, $activity, 'activity_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Activity $activity): bool|Response
    {
        return $this->canDelete($user, $activity, 'activity_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Activity $activity): bool|Response
    {
        return $this->canForceDelete($user, $activity, 'activity_force_delete');
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
