<?php

namespace App\Policies;

use App\Models\Objective;
use App\Models\User;
use App\Policies\Concerns\ChecksAttachableAccess;
use Illuminate\Auth\Access\Response;

class ObjectivePolicy extends BasePolicy
{
    use ChecksAttachableAccess;

    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'objective_viewAny');
    }

    public function view(?User $user, Objective $objective): bool|Response
    {
        $result = $this->canView($user, $objective, 'objective_view');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $objective->attachable_type, $objective->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'objective_create');
    }

    public function update(?User $user, Objective $objective): bool|Response
    {
        $result = $this->canUpdate($user, $objective, 'objective_update');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $objective->attachable_type, $objective->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }

    public function delete(?User $user, Objective $objective): bool|Response
    {
        $result = $this->canDelete($user, $objective, 'objective_delete');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $objective->attachable_type, $objective->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }

    /**
     * Mise à jour de la progression d'un résultat clé — permission dédiée,
     * plus permissive que `objective_update` (action fréquente, voir §2 du plan).
     */
    public function updateKeyResult(?User $user, Objective $objective): bool|Response
    {
        $result = $this->canUpdate($user, $objective, 'key_result_update');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $objective->attachable_type, $objective->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }
}
