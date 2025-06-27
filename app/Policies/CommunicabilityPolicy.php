<?php

namespace App\Policies;

use App\Models\Communicability;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class CommunicabilityPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'communicability_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Communicability $communicability): bool|Response
    {
        return $this->canView($user, $communicability, 'communicability_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'communicability_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Communicability $communicability): bool|Response
    {
        return $this->canUpdate($user, $communicability, 'communicability_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Communicability $communicability): bool|Response
    {
        return $this->canDelete($user, $communicability, 'communicability_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Communicability $communicability): bool|Response
    {
        return $this->canForceDelete($user, $communicability, 'communicability_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Communicability $communicability): bool
    {
        $cacheKey = "communicability_org_access:{$user->id}:{$communicability->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $communicability) {
            // For models directly linked to organisations
            if (method_exists($communicability, 'organisations')) {
                foreach($communicability->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($communicability->organisation_id)) {
                return $communicability->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($communicability, 'activity') && $communicability->activity) {
                foreach($communicability->activity->organisations as $organisation) {
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
