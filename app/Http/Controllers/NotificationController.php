<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher les notifications de l'utilisateur.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications();

        // Filtrage par statut lu/non lu
        if ($request->has('read')) {
            $isRead = $request->boolean('read');
            $query->where('is_read', $isRead);
        }

        // Filtrage par priorité
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Marquer une notification comme lue.
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas marquer cette notification comme lue.');
        }

        $notification->is_read = true;
        $notification->save();

        return back()->with('success', 'Notification marquée comme lue.');
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function markAllAsRead()
    {
        Auth::user()->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * S'abonner à des notifications.
     */
    public function subscribe(Request $request, string $channel, string $entityType, int $entityId)
    {
        $validated = $request->validate([
            'subscription_level' => 'required|string',
        ]);

        // Vérifier que l'abonnement n'existe pas déjà
        $existingSubscription = NotificationSubscription::where([
            'user_id' => Auth::id(),
            'channel' => $channel,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ])->first();

        if ($existingSubscription) {
            // Mettre à jour l'abonnement existant
            $existingSubscription->subscription_level = $validated['subscription_level'];
            $existingSubscription->save();
            $message = 'Abonnement mis à jour avec succès.';
        } else {
            // Créer un nouvel abonnement
            $subscription = new NotificationSubscription();
            $subscription->user_id = Auth::id();
            $subscription->channel = $channel;
            $subscription->entity_type = $entityType;
            $subscription->entity_id = $entityId;
            $subscription->subscription_level = $validated['subscription_level'];
            $subscription->save();
            $message = 'Abonnement créé avec succès.';
        }

        return back()->with('success', $message);
    }

    /**
     * Se désabonner des notifications.
     */
    public function unsubscribe(NotificationSubscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer cet abonnement.');
        }

        $subscription->delete();

        return back()->with('success', 'Désabonnement effectué avec succès.');
    }
}
