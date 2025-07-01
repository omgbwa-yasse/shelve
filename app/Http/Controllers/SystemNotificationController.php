<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemNotificationController extends Controller
{
    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Afficher les notifications système.
     */
    public function index(Request $request)
    {
        $query = SystemNotification::query();

        // Filtrage par priorité
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filtrage par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrage par catégorie
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('notifications.system.index', compact('notifications'));
    }

    /**
     * Afficher une notification système spécifique.
     */
    public function show(SystemNotification $notification)
    {
        return view('notifications.system.show', compact('notification'));
    }

    /**
     * Mettre à jour le statut d'une notification système.
     */
    public function update(Request $request, SystemNotification $notification)
    {
        $this->authorize('update', $notification);

        $validated = $request->validate([
            'status' => 'required|string',
            'resolution_note' => 'nullable|string',
        ]);

        $notification->status = $validated['status'];

        if ($validated['status'] === 'resolved') {
            $notification->resolved_at = now();
            $notification->resolved_by = Auth::id();
            $notification->resolution_note = $validated['resolution_note'] ?? null;
        }

        $notification->save();

        return redirect()
            ->route('workflows.notifications.system.show', $notification)
            ->with('success', 'Notification mise à jour avec succès.');
    }
    
    /**
     * Marquer une notification système comme lue.
     */
    public function markAsRead(SystemNotification $notification)
    {
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue.'
        ]);
    }
    
    /**
     * Marquer toutes les notifications système comme lues.
     */
    public function markAllAsRead()
    {
        SystemNotification::whereNull('read_at')
            ->update(['read_at' => now()]);
            
        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications ont été marquées comme lues.'
        ]);
    }
    
    /**
     * Supprimer une notification système.
     */
    public function destroy(SystemNotification $notification)
    {
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification supprimée avec succès.'
        ]);
    }
}
