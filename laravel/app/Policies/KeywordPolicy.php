<?php

namespace App\Policies;

use App\Models\Keyword;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux mots-clés (référentiel D01).
 *
 * Créée lors du portage de D01 : `KeywordController` renvoyait déjà du JSON sans
 * aucun contrôle d'autorisation. L'API ne peut pas reprendre ce comportement (R04).
 */
class KeywordPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'keyword_viewAny');
    }

    public function view(?User $user, Keyword $keyword): bool|Response
    {
        return $this->canView($user, $keyword, 'keyword_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'keyword_create');
    }

    public function update(?User $user, Keyword $keyword): bool|Response
    {
        return $this->canUpdate($user, $keyword, 'keyword_update');
    }

    public function delete(?User $user, Keyword $keyword): bool|Response
    {
        return $this->canDelete($user, $keyword, 'keyword_delete');
    }

    public function forceDelete(?User $user, Keyword $keyword): bool|Response
    {
        return $this->canForceDelete($user, $keyword, 'keyword_force_delete');
    }
}
