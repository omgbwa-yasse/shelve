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
        $result = $this->canViewAny($user, 'bulletinboards.view');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can view the bulletin board.
     */
    public function view(?User $user, BulletinBoard $bulletinBoard): Response
    {
        $result = $this->canView($user, $bulletinBoard, 'bulletinboards.view');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can create bulletin boards.
     */
    public function create(?User $user): Response
    {
        $result = $this->canCreate($user, 'bulletinboards.create');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can update the bulletin board.
     */
    public function update(?User $user, BulletinBoard $bulletinBoard): Response
    {
        $result = $this->canUpdate($user, $bulletinBoard, 'bulletinboards.update');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can delete the bulletin board.
     */
    public function delete(?User $user, BulletinBoard $bulletinBoard): Response
    {
        $result = $this->canDelete($user, $bulletinBoard, 'bulletinboards.delete');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can restore the bulletin board.
     */
    public function restore(?User $user, BulletinBoard $bulletinBoard): Response
    {
        $result = $this->canUpdate($user, $bulletinBoard, 'bulletinboards.restore');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can permanently delete the bulletin board.
     */
    public function forceDelete(?User $user, BulletinBoard $bulletinBoard): Response
    {
        $result = $this->canForceDelete($user, $bulletinBoard, 'bulletinboards.force-delete');
        return is_bool($result) ? $this->allow() : $result;
    }
}
