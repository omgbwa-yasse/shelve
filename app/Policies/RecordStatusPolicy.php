<?php

namespace App\Policies;

use App\Models\RecordStatus;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

/**
 * Politique d'accès aux statuts de notices (D02, sous-référentiel global).
 */
class RecordStatusPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'record_status_viewAny');
    }

    public function view(?User $user, RecordStatus $recordStatus): bool|Response
    {
        return $this->canView($user, $recordStatus, 'record_status_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'record_status_create');
    }

    public function update(?User $user, RecordStatus $recordStatus): bool|Response
    {
        return $this->canUpdate($user, $recordStatus, 'record_status_update');
    }

    public function delete(?User $user, RecordStatus $recordStatus): bool|Response
    {
        return $this->canDelete($user, $recordStatus, 'record_status_delete');
    }

    public function forceDelete(?User $user, RecordStatus $recordStatus): bool|Response
    {
        return $this->canForceDelete($user, $recordStatus, 'record_status_force_delete');
    }
}
