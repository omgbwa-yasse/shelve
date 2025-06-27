<?php

namespace App\Policies;

use App\Models\Communication;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class CommunicationPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'communication_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Communication $communication): bool|Response
    {
        return $this->canView($user, $communication, 'communication_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'communication_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Communication $communication): bool|Response
    {
        return $this->canUpdate($user, $communication, 'communication_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Communication $communication): bool|Response
    {
        return $this->canDelete($user, $communication, 'communication_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Communication $communication): bool|Response
    {
        return $this->canForceDelete($user, $communication, 'communication_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Communication $communication): bool
    {
        $cacheKey = "communication_org_access:{$user->id}:{$communication->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $communication) {
            // For models directly linked to organisations
            if (method_exists($communication, 'organisations')) {
                foreach($communication->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($communication->organisation_id)) {
                return $communication->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($communication, 'activity') && $communication->activity) {
                foreach($communication->activity->organisations as $organisation) {
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
