<?php

namespace App\Policies;

use App\Models\ReferenceValue;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux valeurs de listes de référence (référentiel D01).
 *
 * Les permissions sont celles de `reference_list_*` : une valeur n'a pas de sens hors
 * de sa liste, et distinguer les deux jeux de droits créerait des combinaisons
 * incohérentes.
 */
class ReferenceValuePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'reference_list_viewAny');
    }

    public function view(?User $user, ReferenceValue $value): bool|Response
    {
        return $this->canView($user, $value, 'reference_list_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'reference_list_create');
    }

    public function update(?User $user, ReferenceValue $value): bool|Response
    {
        return $this->canUpdate($user, $value, 'reference_list_update');
    }

    public function delete(?User $user, ReferenceValue $value): bool|Response
    {
        return $this->canDelete($user, $value, 'reference_list_delete');
    }

    public function forceDelete(?User $user, ReferenceValue $value): bool|Response
    {
        return $this->canForceDelete($user, $value, 'reference_list_force_delete');
    }
}
