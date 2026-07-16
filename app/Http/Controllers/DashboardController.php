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
            'unread' => Mail::whereIn('mail_type', [Mail::TYPE_INCOMING, Mail::TYPE_INTERNAL])
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

            // Sortants ET notes internes (communications inter-services) suivent le
            // même circuit de visa N+1 — seule la signature DG est réservée aux sortants.
            'to_validate' => Mail::whereIn('mail_type', [Mail::TYPE_OUTGOING, Mail::TYPE_INTERNAL])
                ->where('status', MailStatusEnum::PENDING_REVIEW)
                ->where('assigned_to', $user->id)
                ->count(),

            'to_confirm' => Mail::whereIn('mail_type', [Mail::TYPE_INCOMING, Mail::TYPE_INTERNAL])
                ->where('assigned_organisation_id', $organisationId)
                ->whereNot('status', MailStatusEnum::COMPLETED)
                ->count(),

            'to_fix' => Mail::whereIn('mail_type', [Mail::TYPE_OUTGOING, Mail::TYPE_INTERNAL])
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

                // Service : réception à valider (entrants externes + notes internes reçues).
                $q->orWhere(function ($sub) use ($organisationId) {
                    $sub->whereIn('mail_type', [Mail::TYPE_INCOMING, Mail::TYPE_INTERNAL])
                        ->where('assigned_organisation_id', $organisationId)
                        ->whereNot('status', MailStatusEnum::COMPLETED);
                });

                // Validateur courant : sortants ou notes internes en attente de mon visa.
                $q->orWhere(function ($sub) use ($user) {
                    $sub->whereIn('mail_type', [Mail::TYPE_OUTGOING, Mail::TYPE_INTERNAL])
                        ->where('status', MailStatusEnum::PENDING_REVIEW)
                        ->where('assigned_to', $user->id);
                });

                // Initiateur : sortants ou notes internes rejetés à reprendre.
                $q->orWhere(function ($sub) use ($user) {
                    $sub->whereIn('mail_type', [Mail::TYPE_OUTGOING, Mail::TYPE_INTERNAL])
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
