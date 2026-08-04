<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use App\Models\Mail;
use App\Models\MailCotation;
use App\Models\MailHistory;
use App\Models\OrganisationInterim;
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

            // Réception à valider : cotations encore en attente pour ma direction —
            // ou pour une direction dont j'assure l'intérim, dans la limite de mon
            // volet (handledBy) — + courriers/notes affectés sans ligne de cotation.
            'to_confirm' => \App\Models\MailCotation::handledBy($user)
                    ->where('status', \App\Models\MailCotation::STATUS_PENDING)
                    ->count()
                + Mail::whereIn('mail_type', [Mail::TYPE_INCOMING, Mail::TYPE_INTERNAL])
                    ->where('assigned_organisation_id', $organisationId)
                    ->whereNot('status', MailStatusEnum::COMPLETED)
                    ->whereDoesntHave('cotations')
                    ->count(),

            'to_fix' => Mail::whereIn('mail_type', [Mail::TYPE_OUTGOING, Mail::TYPE_INTERNAL])
                ->where('sender_user_id', $user->id)
                ->where('status', MailStatusEnum::REJECTED)
                ->count(),

            // Délais dépassés : échéance passée alors que le courrier n'est pas clos.
            'overdue' => $this->overdueQuery($user, $organisationId, $isDg)->count(),
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

                // Cotations dont j'ai la charge (ma direction ou mon volet d'intérim).
                $q->orWhereHas('cotations', function ($sub) use ($user) {
                    $sub->handledBy($user)->where('status', \App\Models\MailCotation::STATUS_PENDING);
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
        // Ce bloc n'était pas cloisonné : un agent voyait passer l'activité de
        // toutes les directions. On le restreint au périmètre de l'utilisateur.
        $recentActivities = MailHistory::with(['user', 'mail'])
            ->when(!$isDg, fn ($q) => $q->whereHas(
                'mail',
                fn ($m) => $m->forOrganisation($organisationId)
            ))
            ->latest()
            ->limit(6)
            ->get();

        // Courriers en retard, listés pour agir tout de suite.
        $overdueMails = $this->overdueQuery($user, $organisationId, $isDg)
            ->with(['assignedOrganisation', 'typology'])
            ->orderBy('deadline')
            ->limit(6)
            ->get();

        // Suivi des cotations multi-directions : où en est chaque courrier coté ?
        $cotationSuivi = collect();
        if ($isDg) {
            $cotationSuivi = Mail::query()
                ->where('mail_type', Mail::TYPE_INCOMING)
                ->whereHas('cotations')
                ->with(['cotations.organisation'])
                ->withCount([
                    'cotations as cotations_total',
                    'cotations as cotations_done' => fn ($q) => $q->where('status', MailCotation::STATUS_COMPLETED),
                ])
                ->orderByDesc('assigned_at')
                ->limit(5)
                ->get();
        }

        // Intérims que cet utilisateur assure actuellement (volets délégués).
        $interimVolets = OrganisationInterim::activeVoletsOf($user->id)
            ->load(['organisation', 'activity', 'titular']);

        return view('dashboard', compact(
            'stats', 'recentActivities', 'mailStats', 'priorityMails', 'isDg',
            'overdueMails', 'cotationSuivi', 'interimVolets'
        ));
    }

    /**
     * Courriers dont l'échéance est dépassée et qui ne sont pas clos.
     * Le DG voit l'ensemble ; un agent ne voit que ceux de sa direction.
     */
    private function overdueQuery($user, ?int $organisationId, bool $isDg)
    {
        $clos = [MailStatusEnum::COMPLETED, MailStatusEnum::TRANSMITTED, MailStatusEnum::CANCELLED];

        return Mail::query()
            ->whereNotNull('deadline')
            ->whereDate('deadline', '<', now())
            ->whereNotIn('status', $clos)
            ->when(!$isDg, function ($q) use ($organisationId, $user) {
                $q->where(function ($sub) use ($organisationId, $user) {
                    $sub->where('assigned_organisation_id', $organisationId)
                        ->orWhere('recipient_organisation_id', $organisationId)
                        ->orWhere('sender_user_id', $user->id)
                        ->orWhereHas('cotations', fn ($c) => $c->handledBy($user));
                });
            });
    }
}
