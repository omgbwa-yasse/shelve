<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MailNotificationService;
use App\Models\MailNotification;
use Illuminate\Support\Facades\Auth;

class MailNotificationController extends Controller
{
    public function __construct(private MailNotificationService $notificationService)
    {
    }

    /**
     * Afficher les notifications de l'utilisateur
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $unreadOnly = $request->boolean('unread', false);
        $limit = $request->integer('limit', 50);

        if ($unreadOnly) {
            $notifications = $this->notificationService->getUnreadNotifications($user, $limit);
        } else {
            $notifications = MailNotification::forUser($user->id)
                                           ->with('mail')
                                           ->byPriority()
                                           ->orderBy('created_at', 'desc')
                                           ->limit($limit)
                                           ->get();
        }

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $this->notificationService->getUnreadNotifications($user)->count()
        ]);
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function unreadCount()
    {
        $count = $this->notificationService->getUnreadNotifications(Auth::user())->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = MailNotification::where('id', $id)
                                      ->where('user_id', Auth::id())
                                      ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marquée comme lue']);
    }

    /**
     * Marquer plusieurs notifications comme lues
     */
    public function markMultipleAsRead(Request $request)
    {
        $request->validate([
            'notification_ids' => 'required|array',
            'notification_ids.*' => 'integer|exists:mail_notifications,id'
        ]);

        $count = $this->notificationService->markAsRead($request->notification_ids);

        return response()->json([
            'message' => "{$count} notifications marquées comme lues",
            'count' => $count
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        $count = MailNotification::where('user_id', Auth::id())
                                ->whereNull('read_at')
                                ->update(['read_at' => now()]);

        return response()->json([
            'message' => "Toutes les notifications ont été marquées comme lues",
            'count' => $count
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy($id)
    {
        $notification = MailNotification::where('id', $id)
                                      ->where('user_id', Auth::id())
                                      ->firstOrFail();

        $notification->delete();

        return response()->json(['message' => 'Notification supprimée']);
    }

    /**
     * Vue pour afficher les notifications dans l'interface
     */
    public function show(Request $request)
    {
        $notifications = $this->notificationService->getUnreadNotifications(Auth::user(), 20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * API pour les notifications en temps réel (polling)
     */
    public function poll()
    {
        $user = Auth::user();
        $notifications = $this->notificationService->getUnreadNotifications($user, 10);

        return response()->json([
            'notifications' => $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type->value,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'priority' => $notification->priority,
                    'icon' => $notification->type->icon(),
                    'created_at' => $notification->created_at->diffForHumans(),
                    'mail' => [
                        'id' => $notification->mail->id,
                        'code' => $notification->mail->code,
                        'name' => $notification->mail->name,
                    ]
                ];
            }),
            'count' => $notifications->count(),
            'timestamp' => now()->timestamp
        ]);
    }
}
