<?php

namespace App\Policies;

use App\Models\AiRoutine;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AiRoutinePolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'ai_routine_viewAny');
    }

    public function view(?User $user, AiRoutine $routine): bool|Response
    {
        return $this->canView($user, $routine, 'ai_routine_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'ai_routine_create');
    }

    public function update(?User $user, AiRoutine $routine): bool|Response
    {
        return $this->canUpdate($user, $routine, 'ai_routine_update');
    }

    public function delete(?User $user, AiRoutine $routine): bool|Response
    {
        return $this->canDelete($user, $routine, 'ai_routine_delete');
    }
}
