<?php

namespace App\Policies;

use App\Models\WorkplaceTemplate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class WorkplaceTemplatePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'workplace_template_viewAny');
    }

    public function view(?User $user, WorkplaceTemplate $workplaceTemplate): bool|Response
    {
        return $this->canView($user, $workplaceTemplate, 'workplace_template_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'workplace_template_create');
    }

    public function update(?User $user, WorkplaceTemplate $workplaceTemplate): bool|Response
    {
        return $this->canUpdate($user, $workplaceTemplate, 'workplace_template_update');
    }

    public function delete(?User $user, WorkplaceTemplate $workplaceTemplate): bool|Response
    {
        return $this->canDelete($user, $workplaceTemplate, 'workplace_template_delete');
    }

    public function forceDelete(?User $user, WorkplaceTemplate $workplaceTemplate): bool|Response
    {
        return $this->canForceDelete($user, $workplaceTemplate, 'workplace_template_force_delete');
    }
}
