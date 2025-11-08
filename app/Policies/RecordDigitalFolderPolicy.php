<?php

namespace App\Policies;

use App\Models\RecordDigitalFolder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecordDigitalFolderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any folders.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_folders_view');
    }

    /**
     * Determine if the user can view the folder.
     */
    public function view(User $user, RecordDigitalFolder $folder): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_folders_view');
    }

    /**
     * Determine if the user can create folders.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_folders_create');
    }

    /**
     * Determine if the user can update the folder.
     */
    public function update(User $user, RecordDigitalFolder $folder): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_folders_edit');
    }

    /**
     * Determine if the user can delete the folder.
     */
    public function delete(User $user, RecordDigitalFolder $folder): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_folders_delete');
    }

    /**
     * Determine if the user can restore the folder.
     */
    public function restore(User $user, RecordDigitalFolder $folder): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_folders_restore');
    }

    /**
     * Determine if the user can permanently delete the folder.
     */
    public function forceDelete(User $user, RecordDigitalFolder $folder): bool
    {
        return $user->hasRole('superadmin') ||
               $user->can('digital_folders_force_delete');
    }
}
