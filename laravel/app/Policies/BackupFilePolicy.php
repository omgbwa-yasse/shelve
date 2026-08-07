<?php

namespace App\Policies;

use App\Models\BackupFile;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class BackupFilePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'backup_file_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, BackupFile $backupFile): bool|Response
    {
        return $this->canView($user, $backupFile, 'backup_file_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'backup_file_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, BackupFile $backupFile): bool|Response
    {
        return $this->canUpdate($user, $backupFile, 'backup_file_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, BackupFile $backupFile): bool|Response
    {
        return $this->canDelete($user, $backupFile, 'backup_file_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, BackupFile $backupFile): bool|Response
    {
        return $this->canForceDelete($user, $backupFile, 'backup_file_force_delete');
    }
}
