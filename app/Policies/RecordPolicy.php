<?php

namespace App\Policies;

use App\Models\Record;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecordPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'record_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Record $record): bool|Response
    {
        return $this->canView($user, $record, 'record_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'record_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Record $record): bool|Response
    {
        return $this->canUpdate($user, $record, 'record_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Record $record): bool|Response
    {
        return $this->canDelete($user, $record, 'record_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Record $record): bool|Response
    {
        return $this->canForceDelete($user, $record, 'record_force_delete');
    }
}
