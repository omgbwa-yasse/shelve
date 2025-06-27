<?php

namespace App\Policies;

use App\Models\Transferring;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class TransferringPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'transferring_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transferring $transferring): bool|Response
    {
        return $this->canView($user, $transferring, 'transferring_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'transferring_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transferring $transferring): bool|Response
    {
        return $this->canUpdate($user, $transferring, 'transferring_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transferring $transferring): bool|Response
    {
        return $this->canDelete($user, $transferring, 'transferring_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Transferring $transferring): bool|Response
    {
        return $this->canForceDelete($user, $transferring, 'transferring_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Transferring $transferring): bool
    {
        $cacheKey = "transferring_org_access:{$user->id}:{$transferring->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $transferring) {
            // For models directly linked to organisations
            if (method_exists($transferring, 'organisations')) {
                foreach($transferring->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($transferring->organisation_id)) {
                return $transferring->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($transferring, 'activity') && $transferring->activity) {
                foreach($transferring->activity->organisations as $organisation) {
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
