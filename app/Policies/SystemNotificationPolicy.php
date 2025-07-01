<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SystemNotification;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SystemNotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any system notifications.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $user && $user->can('system-notification.view');
    }

    /**
     * Determine whether the user can view a specific system notification.
     */
    public function view(?User $user, SystemNotification $notification): bool|Response
    {
        return $user && $user->can('system-notification.view');
    }

    /**
     * Determine whether the user can update a system notification.
     */
    public function update(?User $user, SystemNotification $notification): bool|Response
    {
        return $user && $user->can('system-notification.update');
    }
}
