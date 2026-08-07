<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

/**
 * D09 — rattachement agent→rôle (`user_roles`).
 *
 * Pivot global : pas d'isolation par organisation (le gate `access-in-organisation`
 * n'a pas de colonne `organisation_id` à comparer). Découverte automatique par
 * convention (`App\Models\UserRole` → `App\Policies\UserRolePolicy`), sans
 * inscription dans AuthServiceProvider.
 */
class UserRolePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'user_role_viewAny');
    }

    public function view(?User $user, UserRole $userRole): bool|Response
    {
        return $this->canView($user, $userRole, 'user_role_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'user_role_create');
    }

    public function update(?User $user, UserRole $userRole): bool|Response
    {
        return $this->canUpdate($user, $userRole, 'user_role_update');
    }

    public function delete(?User $user, UserRole $userRole): bool|Response
    {
        return $this->canDelete($user, $userRole, 'user_role_delete');
    }

    public function forceDelete(?User $user, UserRole $userRole): bool|Response
    {
        return $this->canForceDelete($user, $userRole, 'user_role_force_delete');
    }
}
