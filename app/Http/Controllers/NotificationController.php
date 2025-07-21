<?php

namespace App\Http\Controllers;

use App\Enums\NotificationModule;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display user notifications for Bulletin Boards module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function userNotifications(Request $request)
    {
        $userId = Auth::id();

        $notifications = Notification::where('user_id', $userId)
            ->where('module', NotificationModule::BULLETIN_BOARDS->value)
            ->orderBy('created_at', 'desc')
            ->get();

        $title = 'Mes notifications - Bulletin Boards';
        $type = 'user';
        $id = $userId;

        return view('bulletin-boards.notifications.index', compact('notifications', 'title', 'type', 'id'));
    }

    /**
     * Display organization notifications for Bulletin Boards module.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function organisationNotifications(Request $request)
    {
        $organisationId = Auth::currentOrganisationId();

        $notifications = Notification::where('organisation_id', $organisationId)
            ->where('module', NotificationModule::BULLETIN_BOARDS->value)
            ->orderBy('created_at', 'desc')
            ->get();

        $title = 'Notifications de l\'organisation - Bulletin Boards';
        $type = 'organisation';
        $id = $organisationId;

        return view('bulletin-boards.notifications.index', compact('notifications', 'title', 'type', 'id'));
    }
}
