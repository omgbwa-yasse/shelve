<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workplace;
use Illuminate\Auth\Access\Response;

class WorkplacePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return true; // Users can see the list (filtered by controller)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Workplace $workplace): bool|Response
    {
        // Check if user is a member
        if ($workplace->members()->where('user_id', $user->id)->exists()) {
            return true;
        }

        // Check if workplace is public within the organisation
        if ($workplace->is_public && $workplace->organisation_id == $user->current_organisation_id) {
            return true;
        }

        return $this->deny('Vous n\'avez pas accès à ce workspace.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return true; // Any user can create a workplace
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Workplace $workplace): bool|Response
    {
        $member = $workplace->members()->where('user_id', $user->id)->first();

        if ($member && in_array($member->role, ['owner', 'admin'])) {
            return true;
        }

        return $this->deny('Vous n\'avez pas la permission de modifier ce workspace.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Workplace $workplace): bool|Response
    {
        $member = $workplace->members()->where('user_id', $user->id)->first();

        if ($member && $member->role === 'owner') {
            return true;
        }

        return $this->deny('Seul le propriétaire peut supprimer ce workspace.');
    }

    /**
     * Determine whether the user can manage members.
     */
    public function manageMembers(User $user, Workplace $workplace): bool|Response
    {
        $member = $workplace->members()->where('user_id', $user->id)->first();

        if ($member && ($member->role === 'owner' || ($member->role === 'admin' && $member->can_invite))) {
            return true;
        }

        return $this->deny('Vous n\'avez pas la permission de gérer les membres.');
    }

    /**
     * Determine whether the user can manage content (folders/documents).
     */
    public function manageContent(User $user, Workplace $workplace): bool|Response
    {
        $member = $workplace->members()->where('user_id', $user->id)->first();

        if ($member && ($member->can_create_folders || $member->can_create_documents)) {
            return true;
        }

        return $this->deny('Vous n\'avez pas la permission de gérer le contenu.');
    }
}
