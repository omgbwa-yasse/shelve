<?php

namespace App\Policies;

use App\Models\WorkplaceDocument;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class WorkplaceDocumentPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'workplace_document_viewAny');
    }

    public function view(?User $user, WorkplaceDocument $workplaceDocument): bool|Response
    {
        return $this->canView($user, $workplaceDocument, 'workplace_document_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'workplace_document_create');
    }

    public function update(?User $user, WorkplaceDocument $workplaceDocument): bool|Response
    {
        return $this->canUpdate($user, $workplaceDocument, 'workplace_document_update');
    }

    public function delete(?User $user, WorkplaceDocument $workplaceDocument): bool|Response
    {
        return $this->canDelete($user, $workplaceDocument, 'workplace_document_delete');
    }

    public function forceDelete(?User $user, WorkplaceDocument $workplaceDocument): bool|Response
    {
        return $this->canForceDelete($user, $workplaceDocument, 'workplace_document_force_delete');
    }
}
