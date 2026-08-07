<?php

namespace App\Policies;

use App\Models\EmailTag;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmailTagPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'email_tag_viewAny');
    }

    public function view(?User $user, EmailTag $emailTag): bool|Response
    {
        return $this->canView($user, $emailTag, 'email_tag_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'email_tag_create');
    }

    public function update(?User $user, EmailTag $emailTag): bool|Response
    {
        return $this->canUpdate($user, $emailTag, 'email_tag_update');
    }

    public function delete(?User $user, EmailTag $emailTag): bool|Response
    {
        return $this->canDelete($user, $emailTag, 'email_tag_delete');
    }
}
