<?php

namespace App\Policies;

use App\Models\ContainerProperty;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux types de conteneurs (localisation D03).
 */
class ContainerPropertyPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'container_property_viewAny');
    }

    public function view(?User $user, ContainerProperty $property): bool|Response
    {
        return $this->canView($user, $property, 'container_property_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'container_property_create');
    }

    public function update(?User $user, ContainerProperty $property): bool|Response
    {
        return $this->canUpdate($user, $property, 'container_property_update');
    }

    public function delete(?User $user, ContainerProperty $property): bool|Response
    {
        return $this->canDelete($user, $property, 'container_property_delete');
    }

    public function forceDelete(?User $user, ContainerProperty $property): bool|Response
    {
        return $this->canForceDelete($user, $property, 'container_property_force_delete');
    }
}
