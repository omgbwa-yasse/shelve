<?php

namespace App\Policies;

use App\Models\Sort;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux sorts finaux (référentiel D01).
 *
 * Créée lors du portage de D01 : la ressource était exposée par le back-office sans
 * politique d'autorisation, l'API ne peut pas l'être (risque R04).
 * Suit le patron des policies existantes du même domaine (LanguagePolicy, ActivityPolicy).
 */
class SortPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'sort_viewAny');
    }

    public function view(?User $user, Sort $sort): bool|Response
    {
        return $this->canView($user, $sort, 'sort_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'sort_create');
    }

    public function update(?User $user, Sort $sort): bool|Response
    {
        return $this->canUpdate($user, $sort, 'sort_update');
    }

    public function delete(?User $user, Sort $sort): bool|Response
    {
        return $this->canDelete($user, $sort, 'sort_delete');
    }

    public function forceDelete(?User $user, Sort $sort): bool|Response
    {
        return $this->canForceDelete($user, $sort, 'sort_force_delete');
    }
}
