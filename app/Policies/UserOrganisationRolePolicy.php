<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserOrganisationRole;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

/**
 * D09 — rattachement agent→organisation→rôle (`user_organisation_role`).
 *
 * Pivot ORG-SCOPÉ : la table porte `organisation_id`, donc `access-in-organisation`
 * (BasePolicy::checkOrganisationAccess) renvoie 404 pour tout pivot d'une autre
 * organisation que l'organisation courante de l'agent (R03). Découverte automatique
 * par convention (`App\Models\UserOrganisationRole` → `App\Policies\UserOrganisationRolePolicy`),
 * sans inscription dans AuthServiceProvider.
 */
class UserOrganisationRolePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'user_organisation_role_viewAny');
    }

    public function view(?User $user, UserOrganisationRole $userOrganisationRole): bool|Response
    {
        return $this->canView($user, $userOrganisationRole, 'user_organisation_role_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'user_organisation_role_create');
    }

    public function update(?User $user, UserOrganisationRole $userOrganisationRole): bool|Response
    {
        return $this->canUpdate($user, $userOrganisationRole, 'user_organisation_role_update');
    }

    public function delete(?User $user, UserOrganisationRole $userOrganisationRole): bool|Response
    {
        return $this->canDelete($user, $userOrganisationRole, 'user_organisation_role_delete');
    }

    public function forceDelete(?User $user, UserOrganisationRole $userOrganisationRole): bool|Response
    {
        return $this->canForceDelete($user, $userOrganisationRole, 'user_organisation_role_force_delete');
    }
}
