<?php

namespace App\Policies;

use App\Models\RecordContainer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès à la pivot notice ↔ contenant (domaine D02).
 *
 * Créée lors du portage de D02 : la pivot `record_physical_container` est org-scopée
 * par héritage de sa notice parente (motif D03) — l'isolation est appliquée dans le
 * contrôleur, jamais dans la policy. Préfixe snake_case `record_container`.
 */
class RecordContainerPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'record_container_viewAny');
    }

    public function view(?User $user, RecordContainer $recordContainer): bool|Response
    {
        return $this->canView($user, $recordContainer, 'record_container_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'record_container_create');
    }

    public function update(?User $user, RecordContainer $recordContainer): bool|Response
    {
        return $this->canUpdate($user, $recordContainer, 'record_container_update');
    }

    public function delete(?User $user, RecordContainer $recordContainer): bool|Response
    {
        return $this->canDelete($user, $recordContainer, 'record_container_delete');
    }

    public function forceDelete(?User $user, RecordContainer $recordContainer): bool|Response
    {
        return $this->canForceDelete($user, $recordContainer, 'record_container_force_delete');
    }
}
