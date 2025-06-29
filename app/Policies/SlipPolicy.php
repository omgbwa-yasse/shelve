<?php

namespace App\Policies;

use App\Models\Slip;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class SlipPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'slip_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Slip $slip): bool|Response
    {
        return $this->canView($user, $slip, 'slip_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'slip_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Slip $slip): bool|Response
    {
        return $this->canUpdate($user, $slip, 'slip_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Slip $slip): bool|Response
    {
        return $this->canDelete($user, $slip, 'slip_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Slip $slip): bool|Response
    {
        return $this->canForceDelete($user, $slip, 'slip_force_delete');
    }";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $slip) {
            // For models directly linked to organisations
            if (method_exists($slip, 'organisations')) {
                foreach($slip->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($slip->organisation_id)) {
                return $slip->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($slip, 'activity') && $slip->activity) {
                foreach($slip->activity->organisations as $organisation) {
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
