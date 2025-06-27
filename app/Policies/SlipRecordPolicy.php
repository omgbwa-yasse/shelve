<?php

namespace App\Policies;

use App\Models\SlipRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class SlipRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('slip_record_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SlipRecord $slip_record): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('slip_record_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('slip_record_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SlipRecord $slip_record): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('slip_record_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SlipRecord $slip_record): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('slip_record_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can restore the model.
     * Note: Uses update permission as there is no restore permission in the seeder
     */
    public function restore(User $user, SlipRecord $slip_record): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('slip_record_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SlipRecord $slip_record): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('slip_record_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, SlipRecord $slip_record): bool
    {
        $cacheKey = "slip_record_org_access:{$user->id}:{$slip_record->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $slip_record) {
            // For models directly linked to organisations
            if (method_exists($slip_record, 'organisations')) {
                foreach($slip_record->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($slip_record->organisation_id)) {
                return $slip_record->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($slip_record, 'activity') && $slip_record->activity) {
                foreach($slip_record->activity->organisations as $organisation) {
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
