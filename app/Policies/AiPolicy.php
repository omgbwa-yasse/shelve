<?php

namespace App\Policies;

use App\Models\Ai;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class AiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('ai_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ai $ai): bool
    {
        return $user->hasPermissionTo('ai_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $ai);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('ai_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ai $ai): bool
    {
        return $user->hasPermissionTo('ai_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $ai);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ai $ai): bool
    {
        return $user->hasPermissionTo('ai_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $ai);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ai $ai): bool
    {
        return $user->hasPermissionTo('ai_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $ai);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Ai $ai): bool
    {
        $cacheKey = "ai_org_access:{$user->id}:{$ai->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $ai) {
            // For models directly linked to organisations
            if (method_exists($ai, 'organisations')) {
                foreach($ai->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($ai->organisation_id)) {
                return $ai->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($ai, 'activity') && $ai->activity) {
                foreach($ai->activity->organisations as $organisation) {
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
