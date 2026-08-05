<?php

namespace App\Policies;

use App\Models\RecordAttachment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Politique d'accès à la pivot notice ↔ pièce jointe (domaine D02).
 *
 * Créée lors du portage de D02 : la pivot `record_physical_attachment` est org-scopée
 * par héritage de sa notice parente (motif D03) — l'isolation est appliquée dans le
 * contrôleur, jamais dans la policy. Préfixe snake_case `record_attachment`.
 */
class RecordAttachmentPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'record_attachment_viewAny');
    }

    public function view(?User $user, RecordAttachment $recordAttachment): bool|Response
    {
        return $this->canView($user, $recordAttachment, 'record_attachment_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'record_attachment_create');
    }

    public function update(?User $user, RecordAttachment $recordAttachment): bool|Response
    {
        return $this->canUpdate($user, $recordAttachment, 'record_attachment_update');
    }

    public function delete(?User $user, RecordAttachment $recordAttachment): bool|Response
    {
        return $this->canDelete($user, $recordAttachment, 'record_attachment_delete');
    }

    public function forceDelete(?User $user, RecordAttachment $recordAttachment): bool|Response
    {
        return $this->canForceDelete($user, $recordAttachment, 'record_attachment_force_delete');
    }
}
