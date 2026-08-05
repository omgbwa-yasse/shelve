<?php

namespace App\Policies;

use App\Models\BackupPlanning;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class BackupPlanningPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'backup_planning_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, BackupPlanning $backupPlanning): bool|Response
    {
        return $this->canView($user, $backupPlanning, 'backup_planning_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'backup_planning_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, BackupPlanning $backupPlanning): bool|Response
    {
        return $this->canUpdate($user, $backupPlanning, 'backup_planning_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, BackupPlanning $backupPlanning): bool|Response
    {
        return $this->canDelete($user, $backupPlanning, 'backup_planning_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, BackupPlanning $backupPlanning): bool|Response
    {
        return $this->canForceDelete($user, $backupPlanning, 'backup_planning_force_delete');
    }
}
