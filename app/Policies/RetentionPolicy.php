<?php

namespace App\Policies;

use App\Models\Retention;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class RetentionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'retention_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Retention $retention): bool|Response
    {
        return $this->canView($user, $retention, 'retention_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'retention_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Retention $retention): bool|Response
    {
        return $this->canUpdate($user, $retention, 'retention_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Retention $retention): bool|Response
    {
        return $this->canDelete($user, $retention, 'retention_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Retention $retention): bool|Response
    {
        return $this->canForceDelete($user, $retention, 'retention_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Retention $retention): bool
    {
        $cacheKey = "retention_org_access:{$user->id}:{$retention->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $retention) {
            // For models directly linked to organisations
            if (method_exists($retention, 'organisations')) {
                foreach($retention->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($retention->organisation_id)) {
                return $retention->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($retention, 'activity') && $retention->activity) {
                foreach($retention->activity->organisations as $organisation) {
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
