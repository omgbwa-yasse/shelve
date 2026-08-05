<?php

namespace App\Policies;

use App\Models\ContainerStatus;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux statuts de conteneurs (localisation D03).
 */
class ContainerStatusPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'container_status_viewAny');
    }

    public function view(?User $user, ContainerStatus $status): bool|Response
    {
        return $this->canView($user, $status, 'container_status_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'container_status_create');
    }

    public function update(?User $user, ContainerStatus $status): bool|Response
    {
        return $this->canUpdate($user, $status, 'container_status_update');
    }

    public function delete(?User $user, ContainerStatus $status): bool|Response
    {
        return $this->canDelete($user, $status, 'container_status_delete');
    }

    public function forceDelete(?User $user, ContainerStatus $status): bool|Response
    {
        return $this->canForceDelete($user, $status, 'container_status_force_delete');
    }
}
