<?php

namespace App\Policies;

use App\Models\Dolly;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class DollyPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'dolly_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dolly $dolly): bool|Response
    {
        return $this->canView($user, $dolly, 'dolly_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'dolly_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dolly $dolly): bool|Response
    {
        return $this->canUpdate($user, $dolly, 'dolly_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dolly $dolly): bool|Response
    {
        return $this->canDelete($user, $dolly, 'dolly_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dolly $dolly): bool|Response
    {
        return $this->canForceDelete($user, $dolly, 'dolly_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Dolly $dolly): bool
    {
        $cacheKey = "dolly_org_access:{$user->id}:{$dolly->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $dolly) {
            // For models directly linked to organisations
            if (method_exists($dolly, 'organisations')) {
                foreach($dolly->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($dolly->organisation_id)) {
                return $dolly->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($dolly, 'activity') && $dolly->activity) {
                foreach($dolly->activity->organisations as $organisation) {
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
