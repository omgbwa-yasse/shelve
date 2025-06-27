<?php

namespace App\Policies;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class OrganisationPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'organisation_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organisation $organisation): bool|Response
    {
        return $this->canView($user, $organisation, 'organisation_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'organisation_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organisation $organisation): bool|Response
    {
        return $this->canUpdate($user, $organisation, 'organisation_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organisation $organisation): bool|Response
    {
        return $this->canDelete($user, $organisation, 'organisation_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Organisation $organisation): bool|Response
    {
        return $this->canForceDelete($user, $organisation, 'organisation_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Organisation $organisation): bool
    {
        $cacheKey = "organisation_org_access:{$user->id}:{$organisation->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $organisation) {
            // For models directly linked to organisations
            if (method_exists($organisation, 'organisations')) {
                foreach($organisation->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($organisation->organisation_id)) {
                return $organisation->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($organisation, 'activity') && $organisation->activity) {
                foreach($organisation->activity->organisations as $organisation) {
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
