<?php

namespace App\Policies;

use App\Models\LawArticle;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès aux articles de loi (référentiel D01).
 *
 * Les permissions sont celles de `law_*` : un article n'a pas de sens hors de sa loi,
 * et distinguer les deux jeux de droits créerait des combinaisons incohérentes
 * (voir une loi sans pouvoir voir ses articles).
 */
class LawArticlePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'law_viewAny');
    }

    public function view(?User $user, LawArticle $lawArticle): bool|Response
    {
        return $this->canView($user, $lawArticle, 'law_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'law_create');
    }

    public function update(?User $user, LawArticle $lawArticle): bool|Response
    {
        return $this->canUpdate($user, $lawArticle, 'law_update');
    }

    public function delete(?User $user, LawArticle $lawArticle): bool|Response
    {
        return $this->canDelete($user, $lawArticle, 'law_delete');
    }

    public function forceDelete(?User $user, LawArticle $lawArticle): bool|Response
    {
        return $this->canForceDelete($user, $lawArticle, 'law_force_delete');
    }
}
