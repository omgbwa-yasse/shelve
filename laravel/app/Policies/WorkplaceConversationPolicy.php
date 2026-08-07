<?php

namespace App\Policies;

use App\Models\WorkplaceConversation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class WorkplaceConversationPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'workplace_conversation_viewAny');
    }

    public function view(?User $user, WorkplaceConversation $workplaceConversation): bool|Response
    {
        return $this->canView($user, $workplaceConversation, 'workplace_conversation_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'workplace_conversation_create');
    }

    public function update(?User $user, WorkplaceConversation $workplaceConversation): bool|Response
    {
        return $this->canUpdate($user, $workplaceConversation, 'workplace_conversation_update');
    }

    public function delete(?User $user, WorkplaceConversation $workplaceConversation): bool|Response
    {
        return $this->canDelete($user, $workplaceConversation, 'workplace_conversation_delete');
    }

    public function forceDelete(?User $user, WorkplaceConversation $workplaceConversation): bool|Response
    {
        return $this->canForceDelete($user, $workplaceConversation, 'workplace_conversation_force_delete');
    }
}
