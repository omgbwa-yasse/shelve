<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowDefinition;
use Illuminate\Auth\Access\Response;

class WorkflowDefinitionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'workflow_template_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, WorkflowDefinition $definition): bool|Response
    {
        return $this->canView($user, $definition, 'workflow_template_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'workflow_template_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, WorkflowDefinition $definition): bool|Response
    {
        return $this->canUpdate($user, $definition, 'workflow_template_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, WorkflowDefinition $definition): bool|Response
    {
        return $this->canDelete($user, $definition, 'workflow_template_delete');
    }
}
