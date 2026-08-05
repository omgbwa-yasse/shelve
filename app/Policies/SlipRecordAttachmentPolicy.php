<?php

namespace App\Policies;

use App\Models\SlipRecordAttachment;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class SlipRecordAttachmentPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'slip_record_attachment_viewAny');
    }

    public function view(?User $user, SlipRecordAttachment $slipRecordAttachment): bool|Response
    {
        return $this->canView($user, $slipRecordAttachment, 'slip_record_attachment_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'slip_record_attachment_create');
    }

    public function update(?User $user, SlipRecordAttachment $slipRecordAttachment): bool|Response
    {
        return $this->canUpdate($user, $slipRecordAttachment, 'slip_record_attachment_update');
    }

    public function delete(?User $user, SlipRecordAttachment $slipRecordAttachment): bool|Response
    {
        return $this->canDelete($user, $slipRecordAttachment, 'slip_record_attachment_delete');
    }

    public function forceDelete(?User $user, SlipRecordAttachment $slipRecordAttachment): bool|Response
    {
        return $this->canForceDelete($user, $slipRecordAttachment, 'slip_record_attachment_force_delete');
    }
}
