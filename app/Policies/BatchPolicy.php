<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class BatchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('batch_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Batch $batch): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('batch_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->currentOrganisation && $user->hasPermissionTo('batch_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Batch $batch): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('batch_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Batch $batch): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('batch_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Batch $batch): bool
    {
        return $user->currentOrganisation &&
            $user->hasPermissionTo('batch_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $record);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Batch $batch): bool
    {
        $cacheKey = "batch_org_access:{$user->id}:{$batch->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $batch) {
            // For models directly linked to organisations
            if (method_exists($batch, 'organisations')) {
                foreach($batch->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($batch->organisation_id)) {
                return $batch->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($batch, 'activity') && $batch->activity) {
                foreach($batch->activity->organisations as $organisation) {
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
