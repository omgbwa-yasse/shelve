<?php

namespace App\Policies;

use App\Models\Tool;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class ToolPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'tool_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Tool $tool): bool|Response
    {
        return $this->canView($user, $tool, 'tool_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'tool_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Tool $tool): bool|Response
    {
        return $this->canUpdate($user, $tool, 'tool_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Tool $tool): bool|Response
    {
        return $this->canDelete($user, $tool, 'tool_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Tool $tool): bool|Response
    {
        return $this->canForceDelete($user, $tool, 'tool_force_delete');
    }";

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
