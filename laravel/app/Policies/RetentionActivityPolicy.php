<?php

namespace App\Policies;

use App\Models\RetentionActivity;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

/**
 * Politique d'accès aux liaisons activité ↔ durée de conservation (pivot D07).
 *
 * Les permissions sont celles de `retention_*` : une liaison n'a pas de sens hors de
 * sa durée de conservation, et distinguer deux jeux de droits créerait des
 * combinaisons incohérentes (voir le précédent LawArticlePolicy pour LawArticle).
 */
class RetentionActivityPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'retention_viewAny');
    }

    public function view(?User $user, RetentionActivity $retentionActivity): bool|Response
    {
        return $this->canView($user, $retentionActivity, 'retention_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'retention_create');
    }

    public function update(?User $user, RetentionActivity $retentionActivity): bool|Response
    {
        return $this->canUpdate($user, $retentionActivity, 'retention_update');
    }

    public function delete(?User $user, RetentionActivity $retentionActivity): bool|Response
    {
        return $this->canDelete($user, $retentionActivity, 'retention_delete');
    }

    public function forceDelete(?User $user, RetentionActivity $retentionActivity): bool|Response
    {
        return $this->canForceDelete($user, $retentionActivity, 'retention_force_delete');
    }
}
