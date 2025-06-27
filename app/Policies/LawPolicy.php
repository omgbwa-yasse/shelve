<?php

namespace App\Policies;

use App\Models\Law;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class LawPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'law_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Law $law): bool|Response
    {
        return $this->canView($user, $law, 'law_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'law_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Law $law): bool|Response
    {
        return $this->canUpdate($user, $law, 'law_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Law $law): bool|Response
    {
        return $this->canDelete($user, $law, 'law_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Law $law): bool|Response
    {
        return $this->canForceDelete($user, $law, 'law_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Law $law): bool
    {
        $cacheKey = "law_org_access:{$user->id}:{$law->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $law) {
            // For models directly linked to organisations
            if (method_exists($law, 'organisations')) {
                foreach($law->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($law->organisation_id)) {
                return $law->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($law, 'activity') && $law->activity) {
                foreach($law->activity->organisations as $organisation) {
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
