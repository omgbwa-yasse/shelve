<?php

namespace App\Policies;

use App\Models\BulletinBoard;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class BulletinBoardPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any bulletin boards.
     */
    public function viewAny(?User $user): Response
    {
        $result = $this->canViewAny($user, 'bulletinboards_view');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can view the bulletin board.
     */
    public function view(?User $user, BulletinBoard $bulletinBoard): Response
    {
        $result = $this->canView($user, $bulletinBoard, 'bulletinboards_view');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can create bulletin boards.
     */
    public function create(?User $user): Response
    {
        $result = $this->canCreate($user, 'bulletinboards_create');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can update the bulletin board.
     */
    public function update(?User $user, BulletinBoard $bulletinBoard): Response
    {
        $result = $this->canUpdate($user, $bulletinBoard, 'bulletinboards_update');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can delete the bulletin board.
     */
    public function delete(?User $user, BulletinBoard $bulletinBoard): Response
    {
        $result = $this->canDelete($user, $bulletinBoard, 'bulletinboards_delete');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can restore the bulletin board.
     */
    public function restore(?User $user, BulletinBoard $bulletinBoard): Response
    {
        $result = $this->canUpdate($user, $bulletinBoard, 'bulletinboards_restore');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can permanently delete the bulletin board.
     */
    public function forceDelete(?User $user, BulletinBoard $bulletinBoard): Response
    {
        $result = $this->canForceDelete($user, $bulletinBoard, 'bulletinboards_force_delete');
        return is_bool($result) ? $this->allow() : $result;
    }
}
