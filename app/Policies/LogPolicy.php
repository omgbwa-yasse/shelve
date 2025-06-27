<?php

namespace App\Policies;

use App\Models\Log;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class LogPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'log_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Log $log): bool|Response
    {
        return $this->canView($user, $log, 'log_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'log_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Log $log): bool|Response
    {
        return $this->canUpdate($user, $log, 'log_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Log $log): bool|Response
    {
        return $this->canDelete($user, $log, 'log_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Log $log): bool|Response
    {
        return $this->canForceDelete($user, $log, 'log_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Log $log): bool
    {
        $cacheKey = "log_org_access:{$user->id}:{$log->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $log) {
            // For models directly linked to organisations
            if (method_exists($log, 'organisations')) {
                foreach($log->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($log->organisation_id)) {
                return $log->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($log, 'activity') && $log->activity) {
                foreach($log->activity->organisations as $organisation) {
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
