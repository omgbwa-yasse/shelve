<?php

namespace App\Policies;

use App\Models\MailAttachment;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class MailAttachmentPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'mail_attachment_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, MailAttachment $mailAttachment): bool|Response
    {
        return $this->canView($user, $mailAttachment, 'mail_attachment_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'mail_attachment_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, MailAttachment $mailAttachment): bool|Response
    {
        return $this->canUpdate($user, $mailAttachment, 'mail_attachment_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, MailAttachment $mailAttachment): bool|Response
    {
        return $this->canDelete($user, $mailAttachment, 'mail_attachment_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, MailAttachment $mailAttachment): bool|Response
    {
        return $this->canForceDelete($user, $mailAttachment, 'mail_attachment_force_delete');
    }
}
