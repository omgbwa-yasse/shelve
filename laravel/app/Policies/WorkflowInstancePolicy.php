<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkflowInstance;
use Illuminate\Auth\Access\Response;

class WorkflowInstancePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'workflow_instance_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, WorkflowInstance $instance): bool|Response
    {
        return $this->canView($user, $instance, 'workflow_instance_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'workflow_instance_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, WorkflowInstance $instance): bool|Response
    {
        return $this->canUpdate($user, $instance, 'workflow_instance_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, WorkflowInstance $instance): bool|Response
    {
        return $this->canDelete($user, $instance, 'workflow_instance_delete');
    }
}
