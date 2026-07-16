<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use App\Models\Mail;
use App\Models\MailHistory;
use App\Enums\MailStatusEnum;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get statistics
        // NB : pas de compteur « périodiques » — le modèle RecordPeriodic et la route
        // periodicals.index n'existent pas dans l'application.
        $stats = [
            'folders' => RecordDigitalFolder::count(),
            'documents' => RecordDigitalDocument::count(),
        ];

        $user = Auth::user();
        $organisationId = $user->current_organisation_id;
        $isDg = $user->isSuperAdmin() || $user->hasRoleInOrganisation('DG', $organisationId);

        // Bulles de notification du courrier (mêmes règles que MailController::badgeCounts).
        $mailStats = [
            'unread' => Mail::where('mail_type', Mail::TYPE_INCOMING)
                ->where(function ($q) use ($organisationId) {
                    $q->where('recipient_organisation_id', $organisationId)
                      ->orWhere('assigned_organisation_id', $organisationId);
                })
                ->whereIn('status', [MailStatusEnum::TRANSMITTED, MailStatusEnum::IN_PROGRESS])
                ->whereNull('processed_at')
                ->count(),

            'to_cote' => $isDg
                ? Mail::where('mail_type', Mail::TYPE_INCOMING)
                    ->whereNull('assigned_organisation_id')
                    ->where('status', MailStatusEnum::TRANSMITTED)
                    ->count()
                : 0,

            'to_sign' => $isDg
                ? Mail::where('mail_type', Mail::TYPE_OUTGOING)
                    ->where('status', MailStatusEnum::PENDING_APPROVAL)
                    ->count()
                : 0,

            'to_confirm' => Mail::where('mail_type', Mail::TYPE_INCOMING)
                ->where('assigned_organisation_id', $organisationId)
                ->whereNot('status', MailStatusEnum::COMPLETED)
                ->count(),

            'to_fix' => Mail::where('mail_type', Mail::TYPE_OUTGOING)
                ->where('sender_user_id', $user->id)
                ->where('status', MailStatusEnum::REJECTED)
                ->count(),
        ];

        // Accès direct aux courriers demandant une action de cet utilisateur.
        $priorityMails = Mail::query()
            ->with(['typology', 'assignedOrganisation'])
            ->where(function ($q) use ($isDg, $organisationId, $user) {
                // DG : entrants à coter + sortants à signer.
                if ($isDg) {
                    $q->orWhere(function ($sub) {
                        $sub->where('mail_type', Mail::TYPE_INCOMING)
                            ->whereNull('assigned_organisation_id')
                            ->where('status', MailStatusEnum::TRANSMITTED);
                    });
                    $q->orWhere(function ($sub) {
                        $sub->where('mail_type', Mail::TYPE_OUTGOING)
                            ->where('status', MailStatusEnum::PENDING_APPROVAL);
                    });
                }

                // Service : réception à valider.
                $q->orWhere(function ($sub) use ($organisationId) {
                    $sub->where('mail_type', Mail::TYPE_INCOMING)
                        ->where('assigned_organisation_id', $organisationId)
                        ->whereNot('status', MailStatusEnum::COMPLETED);
                });

                // Initiateur : sortants rejetés à reprendre.
                $q->orWhere(function ($sub) use ($user) {
                    $sub->where('mail_type', Mail::TYPE_OUTGOING)
                        ->where('sender_user_id', $user->id)
                        ->where('status', MailStatusEnum::REJECTED);
                });
            })
            ->orderByDesc('date')
            ->limit(8)
            ->get();

        // Dernières actions tracées sur le courrier (traçabilité).
        $recentActivities = MailHistory::with(['user', 'mail'])
            ->latest()
            ->limit(6)
            ->get();

        return view('dashboard', compact('stats', 'recentActivities', 'mailStats', 'priorityMails', 'isDg'));
    }
}
