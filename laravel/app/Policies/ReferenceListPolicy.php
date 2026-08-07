<?php

namespace App\Policies;

use App\Models\ReferenceList;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux listes de référence (référentiel D01).
 *
 * Créée lors du portage de D01 : `Settings\ReferenceListController` n'appliquait aucune
 * autorisation côté Blade (risque R04).
 */
class ReferenceListPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'reference_list_viewAny');
    }

    public function view(?User $user, ReferenceList $referenceList): bool|Response
    {
        return $this->canView($user, $referenceList, 'reference_list_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'reference_list_create');
    }

    public function update(?User $user, ReferenceList $referenceList): bool|Response
    {
        return $this->canUpdate($user, $referenceList, 'reference_list_update');
    }

    public function delete(?User $user, ReferenceList $referenceList): bool|Response
    {
        return $this->canDelete($user, $referenceList, 'reference_list_delete');
    }

    public function forceDelete(?User $user, ReferenceList $referenceList): bool|Response
    {
        return $this->canForceDelete($user, $referenceList, 'reference_list_force_delete');
    }
}
