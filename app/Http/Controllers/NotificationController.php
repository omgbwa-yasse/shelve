<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Obtenir les notifications pour l'organisation courante
     */
    public function getForOrganisation(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $organisationId = $user->current_organisation_id ?? $user->organisation_id;

            if (!$organisationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune organisation associée'
                ], 400);
            }

            $page = $request->get('page', 1);
            $limit = min($request->get('limit', 20), 100); // Max 100
            $onlyUnread = $request->boolean('unread', false);

            $query = Notification::forOrganisation($organisationId)
                ->with(['user', 'organisation'])
                ->orderBy('created_at', 'desc');

            if ($onlyUnread) {
                $query->unread();
            }

            $notifications = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'total_pages' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'has_more' => $notifications->hasMorePages()
                ],
                'unread_count' => $this->notificationService->countUnreadForOrganisation($organisationId)
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération notifications organisation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications'
            ], 500);
        }
    }

    /**
     * Obtenir les notifications pour l'utilisateur courant
     */
    public function getForCurrentUser(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $page = $request->get('page', 1);
            $limit = min($request->get('limit', 20), 100);
            $onlyUnread = $request->boolean('unread', false);

            $query = Notification::forUser($user->id)
                ->with(['user', 'organisation'])
                ->orderBy('created_at', 'desc');

            if ($onlyUnread) {
                $query->unread();
            }

            $notifications = $query->paginate($limit, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'total_pages' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'has_more' => $notifications->hasMorePages()
                ],
                'unread_count' => $this->notificationService->countUnreadForUser($user->id)
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération notifications utilisateur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications'
            ], 500);
        }
    }

    /**
     * Marquer des notifications comme lues
     */
    public function markAsRead(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'notification_ids' => 'required|array',
                'notification_ids.*' => 'integer|exists:notifications,id'
            ]);

            $user = Auth::user();
            $notificationIds = $request->input('notification_ids');

            // Vérifier que l'utilisateur peut modifier ces notifications
            $allowedNotifications = Notification::whereIn('id', $notificationIds)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('organisation_id', $user->current_organisation_id ?? $user->organisation_id);
                })
                ->pluck('id')
                ->toArray();

            if (empty($allowedNotifications)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune notification autorisée trouvée'
                ], 403);
            }

            $updated = $this->notificationService->markAsRead($allowedNotifications);

            return response()->json([
                'success' => true,
                'message' => "{$updated} notification(s) marquée(s) comme lue(s)",
                'updated_count' => $updated
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur marquage notifications lues: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage des notifications'
            ], 500);
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $scope = $request->get('scope', 'user'); // 'user' ou 'organisation'

            if ($scope === 'organisation') {
                $organisationId = $user->current_organisation_id ?? $user->organisation_id;
                if (!$organisationId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aucune organisation associée'
                    ], 400);
                }
                $updated = $this->notificationService->markAllAsReadForOrganisation($organisationId);
            } else {
                $updated = $this->notificationService->markAllAsReadForUser($user->id);
            }

            return response()->json([
                'success' => true,
                'message' => "{$updated} notification(s) marquée(s) comme lue(s)",
                'updated_count' => $updated
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur marquage toutes notifications lues: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage des notifications'
            ], 500);
        }
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $scope = $request->get('scope', 'user');

            if ($scope === 'organisation') {
                $organisationId = $user->current_organisation_id ?? $user->organisation_id;
                if (!$organisationId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Aucune organisation associée'
                    ], 400);
                }
                $count = $this->notificationService->countUnreadForOrganisation($organisationId);
            } else {
                $count = $this->notificationService->countUnreadForUser($user->id);
            }

            return response()->json([
                'success' => true,
                'unread_count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur comptage notifications non lues: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du comptage des notifications'
            ], 500);
        }
    }

    /**
     * Supprimer des notifications anciennes
     */
    public function cleanup(Request $request): JsonResponse
    {
        try {
            $days = $request->get('days', 30);
            $deleted = $this->notificationService->cleanupOld($days);

            return response()->json([
                'success' => true,
                'message' => "{$deleted} notification(s) supprimée(s)",
                'deleted_count' => $deleted
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur nettoyage notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du nettoyage des notifications'
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'une notification spécifique
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $notification = Notification::with(['user', 'organisation'])
                ->where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhere('organisation_id', $user->current_organisation_id ?? $user->organisation_id);
                })
                ->first();

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification non trouvée'
                ], 404);
            }

            // Marquer comme lue si elle ne l'est pas
            if (!$notification->is_read) {
                $notification->update(['is_read' => true]);
            }

            return response()->json([
                'success' => true,
                'data' => $notification
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur récupération notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la notification'
            ], 500);
        }
    }
}
