<?php

namespace App\Policies;

use App\Models\RetentionLawArticle;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

/**
 * Politique d'accès aux exigences réglementaires (pivot rétention ↔ article de loi, D07).
 *
 * Les permissions sont celles de `retention_*` : une exigence n'a pas de sens hors de
 * sa durée de conservation (voir le précédent LawArticlePolicy).
 */
class RetentionLawArticlePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'retention_viewAny');
    }

    public function view(?User $user, RetentionLawArticle $retentionLawArticle): bool|Response
    {
        return $this->canView($user, $retentionLawArticle, 'retention_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'retention_create');
    }

    public function update(?User $user, RetentionLawArticle $retentionLawArticle): bool|Response
    {
        return $this->canUpdate($user, $retentionLawArticle, 'retention_update');
    }

    public function delete(?User $user, RetentionLawArticle $retentionLawArticle): bool|Response
    {
        return $this->canDelete($user, $retentionLawArticle, 'retention_delete');
    }

    public function forceDelete(?User $user, RetentionLawArticle $retentionLawArticle): bool|Response
    {
        return $this->canForceDelete($user, $retentionLawArticle, 'retention_force_delete');
    }
}
