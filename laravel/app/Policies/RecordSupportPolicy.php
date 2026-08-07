<?php

namespace App\Policies;

use App\Models\RecordSupport;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

/**
 * Politique d'accès aux supports de notices (D02, sous-référentiel global).
 */
class RecordSupportPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'record_support_viewAny');
    }

    public function view(?User $user, RecordSupport $recordSupport): bool|Response
    {
        return $this->canView($user, $recordSupport, 'record_support_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'record_support_create');
    }

    public function update(?User $user, RecordSupport $recordSupport): bool|Response
    {
        return $this->canUpdate($user, $recordSupport, 'record_support_update');
    }

    public function delete(?User $user, RecordSupport $recordSupport): bool|Response
    {
        return $this->canDelete($user, $recordSupport, 'record_support_delete');
    }

    public function forceDelete(?User $user, RecordSupport $recordSupport): bool|Response
    {
        return $this->canForceDelete($user, $recordSupport, 'record_support_force_delete');
    }
}
