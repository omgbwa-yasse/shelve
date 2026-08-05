<?php

namespace App\Policies;

use App\Models\RecordType;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

/**
 * Politique d'accès aux types de notices (D02, sous-référentiel global).
 */
class RecordTypePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'record_type_viewAny');
    }

    public function view(?User $user, RecordType $recordType): bool|Response
    {
        return $this->canView($user, $recordType, 'record_type_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'record_type_create');
    }

    public function update(?User $user, RecordType $recordType): bool|Response
    {
        return $this->canUpdate($user, $recordType, 'record_type_update');
    }

    public function delete(?User $user, RecordType $recordType): bool|Response
    {
        return $this->canDelete($user, $recordType, 'record_type_delete');
    }

    public function forceDelete(?User $user, RecordType $recordType): bool|Response
    {
        return $this->canForceDelete($user, $recordType, 'record_type_force_delete');
    }
}
