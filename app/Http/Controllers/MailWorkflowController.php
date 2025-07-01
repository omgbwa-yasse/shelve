<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mail;
use App\Models\MailHistory;
use App\Models\MailWorkflow;
use App\Enums\MailStatusEnum;
use App\Services\MailNotificationService;
use Illuminate\Support\Facades\Auth;

class MailWorkflowController extends Controller
{

    /**
     * Dashboard principal du workflow
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Statistiques générales
        $stats = [
            'assigned_to_me' => Mail::where('assigned_to', $user->id)->count(),
            'overdue' => Mail::whereNotNull('deadline')
                            ->where('deadline', '<', now())
                            ->whereNotIn('status', ['completed', 'cancelled'])
                            ->where('assigned_to', $user->id)
                            ->count(),
            'approaching_deadline' => Mail::whereNotNull('deadline')
                                        ->where('deadline', '>', now())
                                        ->where('deadline', '<=', now()->addHours(24))
                                        ->whereNotIn('status', ['completed', 'cancelled'])
                                        ->where('assigned_to', $user->id)
                                        ->count(),
            'pending_approval' => Mail::where('status', 'pending_approval')
                                    ->where('assigned_to', $user->id)
                                    ->count(),
            'in_progress' => Mail::where('status', 'in_progress')
                                ->where('assigned_to', $user->id)
                                ->count(),
        ];

        // Courriers récents assignés
        $recentMails = Mail::where('assigned_to', $user->id)
                          ->orderBy('assigned_at', 'desc')
                          ->limit(10)
                          ->get();

        // Notifications récentes (simplifié pour éviter les erreurs)
        $recentNotifications = [];

        return view('mails.workflow.dashboard', compact('stats', 'recentMails', 'recentNotifications'));
    }

    /**
     * Courriers en retard
     */
    public function overdue()
    {
        $overdueMails = Mail::overdue()
                    ->with(['assignedTo'])
                    ->orderBy('deadline', 'asc')
                    ->paginate(20);

        return view('mails.workflow.overdue', compact('overdueMails'));
    }

    /**
     * Échéances approchantes
     */
    public function approachingDeadline(Request $request)
    {
        $hours = $request->get('hours', 72); // 3 jours par défaut

        $approachingDeadlineMails = Mail::whereNotNull('deadline')
                    ->where('deadline', '>', now())
                    ->where('deadline', '<=', now()->addHours($hours))
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->with(['assignedTo'])
                    ->orderBy('deadline', 'asc')
                    ->paginate(20);

        return view('mails.workflow.approaching-deadline', compact('approachingDeadlineMails', 'hours'));
    }

    /**
     * Courriers assignés à moi
     */
    public function assignedToMe(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');
        $priority = $request->get('priority');
        $search = $request->get('search');

        $query = Mail::where('assigned_to', $user->id)
                    ->with(['assignedTo']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('object', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%");
            });
        }

        $assignedMails = $query->orderBy('assigned_at', 'desc')->paginate(20);

        // Statistiques pour cette vue
        $stats = [
            'total' => Mail::where('assigned_to', $user->id)->count(),
            'in_progress' => Mail::where('assigned_to', $user->id)->where('status', 'in_progress')->count(),
            'overdue' => Mail::where('assigned_to', $user->id)
                            ->whereNotNull('deadline')
                            ->where('deadline', '<', now())
                            ->whereNotIn('status', ['completed', 'cancelled'])
                            ->count(),
            'completed_today' => Mail::where('assigned_to', $user->id)
                                   ->where('status', 'completed')
                                   ->where('updated_at', '>=', today())
                                   ->count(),
        ];

        return view('mails.workflow.assigned-to-me', compact('assignedMails', 'stats', 'status', 'priority', 'search'));
    }

    /**
     * Historique et audit trail
     */
    public function auditTrail(Request $request)
    {
        $mailId = $request->get('mail_id');
        $action = $request->get('action');
        $userId = $request->get('user_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = MailHistory::with(['mail', 'user']);

        if ($mailId) {
            $query->where('mail_id', $mailId);
        }

        if ($action) {
            $query->where('action', $action);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $auditTrail = $query->orderBy('created_at', 'desc')->paginate(50);

        // Liste des utilisateurs pour le filtre
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('mails.workflow.audit-trail', compact('auditTrail', 'users', 'mailId', 'action', 'userId', 'dateFrom', 'dateTo'));
    }

    /**
     * Mettre à jour le statut d'un courrier
     */
    public function updateStatus(Request $request, Mail $mail)
    {
        $request->validate([
            'status' => 'required|string|in:draft,pending_review,in_progress,pending_approval,approved,transmitted,completed,rejected,cancelled',
        ]);

        $oldStatus = $mail->status;
        $newStatus = $request->status;

        try {
            $mail->update(['status' => $newStatus]);

            // Enregistrer l'historique
            MailHistory::create([
                'mail_id' => $mail->id,
                'user_id' => Auth::id(),
                'action' => 'status_changed',
                'details' => [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
                'ip_address' => request()->ip(),
            ]);

            return back()->with('success', "Statut mis à jour vers {$newStatus}");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Assigner un courrier
     */
    public function assign(Request $request, Mail $mail)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);

        try {
            $oldAssigned = $mail->assigned_to;
            $mail->update([
                'assigned_to' => $request->assigned_to,
                'assigned_at' => now()
            ]);

            // Enregistrer l'historique
            MailHistory::create([
                'mail_id' => $mail->id,
                'user_id' => Auth::id(),
                'action' => 'assigned',
                'details' => [
                    'old_assigned_to' => $oldAssigned,
                    'new_assigned_to' => $request->assigned_to,
                ],
                'ip_address' => request()->ip(),
            ]);

            return back()->with('success', 'Courrier assigné avec succès');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approuver un courrier
     */
    public function approve(Request $request, Mail $mail)
    {
        $request->validate([
            'comments' => 'nullable|string|max:1000'
        ]);

        $workflow = $mail->workflow;

        if (!$workflow || !$workflow->canBeApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce courrier ne peut pas être approuvé'
            ], 400);
        }

        try {
            $workflow->approve(Auth::id(), $request->comments);

            return response()->json([
                'success' => true,
                'message' => 'Courrier approuvé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Rejeter un courrier
     */
    public function reject(Request $request, Mail $mail)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $workflow = $mail->workflow;

        if (!$workflow || !$workflow->canBeApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce courrier ne peut pas être rejeté'
            ], 400);
        }

        try {
            $workflow->reject(Auth::id(), $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Courrier rejeté'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupérer la liste des organisations pour l'assignation
     */
    public function getOrganisations()
    {
        $organisations = \App\Models\Organisation::select('id', 'name')
                                                 ->orderBy('name')
                                                 ->get();

        return response()->json($organisations);
    }

    /**
     * Récupérer les utilisateurs d'une organisation
     */
    public function getOrganisationUsers(\App\Models\Organisation $organisation)
    {
        $users = $organisation->users()
                             ->select('users.id', 'users.name', 'users.email')
                             ->where('users.is_active', true)
                             ->orderBy('users.name')
                             ->get();

        return response()->json($users);
    }

    /**
     * Assigner un courrier via AJAX
     */
    public function assignAjax(Request $request, Mail $mail)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'assigned_organisation_id' => 'required|exists:organisations,id',
            'comment' => 'nullable|string|max:500'
        ]);

        try {
            $oldAssignedTo = $mail->assigned_to;
            $oldOrganisation = $mail->assigned_organisation_id;

            $mail->update([
                'assigned_to' => $request->assigned_to,
                'assigned_organisation_id' => $request->assigned_organisation_id,
                'assigned_at' => now()
            ]);

            // Enregistrer l'historique
            MailHistory::create([
                'mail_id' => $mail->id,
                'user_id' => Auth::id(),
                'action' => 'assigned',
                'details' => [
                    'old_assigned_to' => $oldAssignedTo,
                    'new_assigned_to' => $request->assigned_to,
                    'old_organisation' => $oldOrganisation,
                    'new_organisation' => $request->assigned_organisation_id,
                    'comment' => $request->comment,
                ],
                'ip_address' => request()->ip(),
            ]);

            // Récupérer les informations pour la réponse
            $assignedUser = \App\Models\User::find($request->assigned_to);
            $organisation = \App\Models\Organisation::find($request->assigned_organisation_id);

            return response()->json([
                'success' => true,
                'message' => "Courrier assigné à {$assignedUser->name} ({$organisation->name})",
                'assigned_user' => $assignedUser,
                'assigned_organisation' => $organisation,
                'assigned_at' => now()->format('d/m/Y H:i')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation : ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Afficher le modal d'assignation
     */
    public function assignModal(Mail $mail)
    {
        return view('mails.workflow.assign-modal', compact('mail'));
    }
}
