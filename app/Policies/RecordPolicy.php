<?php

namespace App\Policies;

use App\Models\Record;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('record_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Record $record): bool
    {
        return $user->hasPermissionTo('record_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('record_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Record $record): bool
    {
        return $user->hasPermissionTo('record_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Record $record): bool
    {
        return $user->hasPermissionTo('record_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Record $record): bool
    {
        return $user->hasPermissionTo('record_restore', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Record $record): bool
    {
        return $user->hasPermissionTo('record_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }



    /**
     * Check if the user has access to the record within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Record $record): bool
    {
    foreach($record->activity->organisations as $organisation){
        if ($organisation->id == $user->current_organisation_id) {
        return true;
        }
    }
    return false;
    }

}
