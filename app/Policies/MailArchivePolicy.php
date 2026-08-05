<?php

namespace App\Policies;

use App\Models\MailArchive;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class MailArchivePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'mail_archive_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, MailArchive $mailArchive): bool|Response
    {
        return $this->canView($user, $mailArchive, 'mail_archive_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'mail_archive_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, MailArchive $mailArchive): bool|Response
    {
        return $this->canUpdate($user, $mailArchive, 'mail_archive_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, MailArchive $mailArchive): bool|Response
    {
        return $this->canDelete($user, $mailArchive, 'mail_archive_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, MailArchive $mailArchive): bool|Response
    {
        return $this->canForceDelete($user, $mailArchive, 'mail_archive_force_delete');
    }
}
