<?php

namespace App\Policies;

use App\Models\CommunicationRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class CommunicationRecordPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'communication_record_viewAny');
    }

    public function view(?User $user, CommunicationRecord $communicationRecord): bool|Response
    {
        return $this->canView($user, $communicationRecord, 'communication_record_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'communication_record_create');
    }

    public function update(?User $user, CommunicationRecord $communicationRecord): bool|Response
    {
        return $this->canUpdate($user, $communicationRecord, 'communication_record_update');
    }

    public function delete(?User $user, CommunicationRecord $communicationRecord): bool|Response
    {
        return $this->canDelete($user, $communicationRecord, 'communication_record_delete');
    }

    public function forceDelete(?User $user, CommunicationRecord $communicationRecord): bool|Response
    {
        return $this->canForceDelete($user, $communicationRecord, 'communication_record_force_delete');
    }
}
