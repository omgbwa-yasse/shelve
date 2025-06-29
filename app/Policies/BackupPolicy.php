<?php

namespace App\Policies;

use App\Models\Backup;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class BackupPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'backup_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Backup $backup): bool|Response
    {
        return $this->canView($user, $backup, 'backup_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'backup_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Backup $backup): bool|Response
    {
        return $this->canUpdate($user, $backup, 'backup_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Backup $backup): bool|Response
    {
        return $this->canDelete($user, $backup, 'backup_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Backup $backup): bool|Response
    {
        return $this->canForceDelete($user, $backup, 'backup_force_delete');
    }";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $backup) {
            // For models directly linked to organisations
            if (method_exists($backup, 'organisations')) {
                foreach($backup->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($backup->organisation_id)) {
                return $backup->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($backup, 'activity') && $backup->activity) {
                foreach($backup->activity->organisations as $organisation) {
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
