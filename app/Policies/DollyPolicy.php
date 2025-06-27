<?php

namespace App\Policies;

use App\Models\Dolly;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class DollyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('dolly_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dolly $dolly): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('dolly_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('dolly_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dolly $dolly): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('dolly_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dolly $dolly): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('dolly_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dolly $dolly): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('dolly_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
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
