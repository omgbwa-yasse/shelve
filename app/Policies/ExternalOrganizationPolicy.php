<?php

namespace App\Policies;

use App\Models\ExternalOrganization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux organisations externes (référentiel D01).
 *
 * Créée lors du portage de D01 : `ExternalOrganizationController` n'appliquait aucune
 * autorisation côté Blade (risque R04).
 */
class ExternalOrganizationPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'external_organization_viewAny');
    }

    public function view(?User $user, ExternalOrganization $organization): bool|Response
    {
        return $this->canView($user, $organization, 'external_organization_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'external_organization_create');
    }

    public function update(?User $user, ExternalOrganization $organization): bool|Response
    {
        return $this->canUpdate($user, $organization, 'external_organization_update');
    }

    public function delete(?User $user, ExternalOrganization $organization): bool|Response
    {
        return $this->canDelete($user, $organization, 'external_organization_delete');
    }

    public function forceDelete(?User $user, ExternalOrganization $organization): bool|Response
    {
        return $this->canForceDelete($user, $organization, 'external_organization_force_delete');
    }
}
