<?php

namespace App\Policies;

use App\Models\Tool;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class ToolPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('tool_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tool $tool): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('tool_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('tool_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tool $tool): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('tool_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tool $tool): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('tool_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tool $tool): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('tool_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Tool $tool): bool
    {
        $cacheKey = "tool_org_access:{$user->id}:{$tool->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $tool) {
            // For models directly linked to organisations
            if (method_exists($tool, 'organisations')) {
                foreach($tool->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($tool->organisation_id)) {
                return $tool->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($tool, 'activity') && $tool->activity) {
                foreach($tool->activity->organisations as $organisation) {
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
