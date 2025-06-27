<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class BatchPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'batch_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Batch $batch): bool|Response
    {
        return $this->canView($user, $batch, 'batch_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'batch_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Batch $batch): bool|Response
    {
        return $this->canUpdate($user, $batch, 'batch_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Batch $batch): bool|Response
    {
        return $this->canDelete($user, $batch, 'batch_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Batch $batch): bool|Response
    {
        return $this->canForceDelete($user, $batch, 'batch_force_delete');
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
