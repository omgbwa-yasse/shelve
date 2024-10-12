<?php

namespace App\Http\Controllers;

use App\Models\Accession;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Author;
use App\Models\Communicability;
use App\Models\Communication;
use App\Models\Container;
use App\Models\Dolly;
use App\Models\Mail;
use App\Models\MailAction;
use App\Models\MailAttachment;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordAttachment;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\Slip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function index()
    {
        $reservations = Reservation::with('operator', 'user', 'status', 'userOrganisation', 'operatorOrganisation')->get();
        return view('reservations.index', compact('reservations'));
    }


    public function statisticsMails()
    {
        // Statistiques générales
        $totalMails = Mail::count();
        $sentMails = Mail::where('mail_type_id', 1)->count();
        $receivedMails = Mail::where('mail_type_id', 2)->count();
        $inProgressMails = Mail::where('mail_type_id', 3)->count();

        // Courriers par priorité
        $mailsPriorityData = Mail::select('mail_priority_id', DB::raw('count(*) as count'))
            ->groupBy('mail_priority_id')
            ->pluck('count', 'mail_priority_id')
            ->toArray();
        $mailsPriorityLabels = DB::table('mail_priorities')
            ->whereIn('id', array_keys($mailsPriorityData))
            ->pluck('name', 'id')
            ->toArray();

        // Courriers par type
        $mailsTypeData = Mail::select('mail_type_id', DB::raw('count(*) as count'))
            ->groupBy('mail_type_id')
            ->pluck('count', 'mail_type_id')
            ->toArray();
        $mailsTypeLabels = DB::table('mail_types')
            ->whereIn('id', array_keys($mailsTypeData))
            ->pluck('name', 'id')
            ->toArray();

        // Évolution du nombre de courriers
        $mailsEvolution = Mail::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
        $mailsEvolutionLabels = $mailsEvolution->pluck('date');
        $mailsEvolutionData = $mailsEvolution->pluck('count');

        // Top 5 des organisations
        $topOrganisations = Mail::select('creator_organisation_id', DB::raw('count(*) as count'))
            ->groupBy('creator_organisation_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
        $topOrganisationsLabels = Organisation::whereIn('id', $topOrganisations->pluck('creator_organisation_id'))
            ->pluck('name', 'id')
            ->toArray();
        $topOrganisationsData = $topOrganisations->pluck('count')->toArray();

        // Temps moyen de traitement
        $averageProcessingTime = Mail::whereNotNull('updated_at')
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_time')
            ->value('avg_time');

        $processingTimeByPriority = Mail::whereNotNull('updated_at')
            ->select('mail_priority_id', DB::raw('AVG(DATEDIFF(updated_at, created_at)) as avg_time'))
            ->groupBy('mail_priority_id')
            ->get();
        $processingTimeLabels = $mailsPriorityLabels;
        $processingTimeData = $processingTimeByPriority->pluck('avg_time')->toArray();


        // Statistiques des pièces jointes
        $totalAttachments = MailAttachment::count();
        $averageAttachmentSize = MailAttachment::avg('size') / 1024 / 1024; // Convertir en MB

// Extraire l'extension du chemin et compter
        $attachmentTypeData = MailAttachment::select(
            DB::raw("SUBSTRING_INDEX(path, '.', -1) as extension"),
            DB::raw('count(*) as count')
        )
            ->groupBy(DB::raw("SUBSTRING_INDEX(path, '.', -1)"))
            ->pluck('count', 'extension')
            ->toArray();

        $attachmentTypeLabels = array_keys($attachmentTypeData);

        // Distribution mensuelle des courriers
        $monthlyDistribution = Mail::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();
        $monthlyDistributionLabels = $monthlyDistribution->pluck('month')->map(function($month) {
            return Carbon::create()->month($month)->format('F');
        });
        $monthlyDistributionData = $monthlyDistribution->pluck('count');

        // Top 10 des expéditeurs (utilisant le champ 'contacts' au lieu de 'author')
        // Top 10 des auteurs de courriers
        $topAuthors = Author::select('authors.id', 'authors.name', DB::raw('COUNT(mail_author.mail_id) as mail_count'))
            ->join('mail_author', 'authors.id', '=', 'mail_author.author_id')
            ->groupBy('authors.id', 'authors.name')
            ->orderByDesc('mail_count')
            ->limit(10)
            ->get();

// Préparer les données pour la vue
        $topSendersLabels = $topAuthors->pluck('name');
        $topSendersData = $topAuthors->pluck('mail_count');
        // Actions sur les courriers
        $mailActions = MailAction::select('name', DB::raw('count(*) as count'))
            ->groupBy('name')
            ->orderByDesc('count')
            ->get();
        $mailActionsLabels = $mailActions->pluck('name');
        $mailActionsData = $mailActions->pluck('count');


        return view('report.statistics.mails', compact(
            'totalMails', 'sentMails', 'receivedMails', 'inProgressMails',
            'mailsPriorityLabels', 'mailsPriorityData',
            'mailsTypeLabels', 'mailsTypeData',
            'mailsEvolutionLabels', 'mailsEvolutionData',
            'topOrganisationsLabels', 'topOrganisationsData',
            'averageProcessingTime', 'processingTimeLabels', 'processingTimeData',
            'totalAttachments', 'averageAttachmentSize', 'attachmentTypeLabels', 'attachmentTypeData',
            'monthlyDistributionLabels', 'monthlyDistributionData',
            'mailActionsLabels', 'mailActionsData',
            'topSendersLabels', 'topSendersData'  // Ajoutez ces deux variables ici
        ));
    }

    public function statisticsRepositories()
    {
        // Statistiques générales
        $totalRecords = Record::count();
        $recordsWithContainer = Record::whereHas('containers')->count();
        $recordsWithoutContainer = $totalRecords - $recordsWithContainer;

        // Records par niveau de description
        $recordsByLevel = Record::select('level_id', DB::raw('count(*) as count'))
            ->groupBy('level_id')
            ->get()
            ->pluck('count', 'level_id')
            ->toArray();

        // Records par support
        $recordsBySupport = Record::select('support_id', DB::raw('count(*) as count'))
            ->groupBy('support_id')
            ->get()
            ->pluck('count', 'support_id')
            ->toArray();

        // Évolution du nombre de records
        $recordsEvolution = Record::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
        $recordsEvolutionLabels = $recordsEvolution->pluck('date');
        $recordsEvolutionData = $recordsEvolution->pluck('count');

        // Top 5 des activités liées aux records
        $topActivities = Record::select('activity_id', DB::raw('count(*) as count'))
            ->groupBy('activity_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Distribution des records par statut
        $recordsByStatus = Record::select('status_id', DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->get()
            ->pluck('count', 'status_id')
            ->toArray();

        // Statistiques des pièces jointes
        $totalAttachments = Attachment::count();
        $averageAttachmentSize = Attachment::avg('size') / 1024 / 1024; // Convertir en MB

        // Types de pièces jointes
        $attachmentTypes = Attachment::select(
            DB::raw("SUBSTRING_INDEX(path, '.', -1) as extension"),
            DB::raw('count(*) as count')
        )
            ->groupBy(DB::raw("SUBSTRING_INDEX(path, '.', -1)"))
            ->pluck('count', 'extension')
            ->toArray();

        // Distribution mensuelle des records
        $monthlyDistribution = Record::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();
        $monthlyDistributionLabels = $monthlyDistribution->pluck('month')->map(function($month) {
            return date('F', mktime(0, 0, 0, $month, 1));
        });
        $monthlyDistributionData = $monthlyDistribution->pluck('count');

        // Nouveaux statistiques basés sur le modèle Record
        $recordsWithAuthors = Record::has('authors')->count();
        $recordsWithTerms = Record::has('terms')->count();
        $recordsWithAttachments = Record::has('attachments')->count();

        $topOrganisations = Record::select('organisation_id', DB::raw('count(*) as count'))
            ->groupBy('organisation_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->pluck('count', 'organisation_id')
            ->toArray();

        $recordsWithChildren = Record::has('children')->count();
        $averageChildrenPerRecord = 0;

        // Préparer les données pour les graphiques
        $levelNames = RecordLevel::pluck('name', 'id')->toArray();
        $supportNames = RecordSupport::pluck('name', 'id')->toArray();
        $statusNames = RecordStatus::pluck('name', 'id')->toArray();
        $activityNames = Activity::pluck('name', 'id')->toArray();
        $organisationNames = Organisation::whereIn('id', array_keys($topOrganisations))->pluck('name', 'id')->toArray();

        return view('report.statistics.repositories', compact(
            'totalRecords', 'recordsWithContainer', 'recordsWithoutContainer',
            'recordsByLevel', 'recordsBySupport', 'recordsByStatus',
            'recordsEvolutionLabels', 'recordsEvolutionData',
            'topActivities', 'totalAttachments', 'averageAttachmentSize',
            'attachmentTypes', 'monthlyDistributionLabels', 'monthlyDistributionData',
            'recordsWithAuthors', 'recordsWithTerms', 'recordsWithAttachments',
            'topOrganisations', 'recordsWithChildren', 'averageChildrenPerRecord',
            'levelNames', 'supportNames', 'statusNames', 'activityNames', 'organisationNames'
        ));
    }

    public function statisticsCommunications()
    {
        // Logique pour afficher les statistiques du module Demande
        return view('report.statistics.communications');
    }

    public function statisticsTransferrings()
    {
        // Logique pour afficher les statistiques du module Transfert
        return view('report.statistics.transferrings');
    }

    public function statisticsDeposits()
    {
        // Logique pour afficher les statistiques du module Dépôt
        return view('report.statistics.deposits');
    }

    public function statisticsTools()
    {
        // Logique pour afficher les statistiques du module Outil
        return view('report.statistics.tools');
    }

    public function statisticsDollies()
    {
        // Logique pour afficher les statistiques du module Chariots
        return view('report.statistics.dollies');
    }

    public function dashboard()
    {
        // Communications
        $totalCommunications = Communication::count();
        $pendingCommunications = Communication::where('status_id', 1)->count(); // Assuming 1 is pending status

        // Dollies
        $totalDollies = Dolly::count();
        $dolliesByType = Dolly::query()
            ->select('type_id', DB::raw('count(*) as count'))
            ->groupBy('type_id')
            ->pluck('count', 'type_id');

        // Mails
        $totalMails = Mail::count();
        $mailsByPriority = Mail::query()
            ->select('mail_priority_id', DB::raw('count(*) as count'))
            ->groupBy('mail_priority_id')
            ->pluck('count', 'mail_priority_id');

        // Repository
        $totalRecords = Record::count();
        $recordsByLevel = Record::query()
            ->select('level_id', DB::raw('count(*) as count'))
            ->groupBy('level_id')
            ->pluck('count', 'level_id');

        // Tools
        $totalActivities = Activity::count();
        $totalCommunicabilities = Communicability::count();

        // Transferring (Slips)
        $totalSlips = Slip::count();
        $slipsByStatus = Slip::query()
            ->select('slip_status_id', DB::raw('count(*) as count'))
            ->groupBy('slip_status_id')
            ->pluck('count', 'slip_status_id');

        // Additional statistics
        $totalUsers = User::count();
        $totalOrganisations = Organisation::count();
        $totalContainers = Container::count();

        // Chart data
        $communicationsDates = Communication::query()
            ->select(DB::raw('DATE(created_at) as date'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('date');

        $communicationsData = Communication::query()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count');

        $mailsLabels = Mail::query()
            ->select('mail_priority_id', DB::raw('count(*) as count'))
            ->groupBy('mail_priority_id')
            ->pluck('mail_priority_id');

        $mailsData = Mail::query()
            ->select('mail_priority_id', DB::raw('count(*) as count'))
            ->groupBy('mail_priority_id')
            ->pluck('count');

        $slipsLabels = Slip::query()
            ->select('slip_status_id', DB::raw('count(*) as count'))
            ->groupBy('slip_status_id')
            ->pluck('slip_status_id');

        $slipsData = Slip::query()
            ->select('slip_status_id', DB::raw('count(*) as count'))
            ->groupBy('slip_status_id')
            ->pluck('count');

        return view('report.dashboard', compact(
            'totalCommunications',
            'pendingCommunications',
                        'totalDollies',
                        'dolliesByType',
                        'totalMails',
                        'mailsByPriority',
                        'totalRecords',
                        'recordsByLevel',
                        'totalActivities',
                        'totalCommunicabilities',
                        'totalSlips',
                        'slipsByStatus',
                        'totalUsers',
                        'totalOrganisations',
                        'totalContainers',
                        'communicationsDates',
                        'communicationsData',
                        'mailsLabels',
                        'mailsData',
                        'slipsLabels',
                        'slipsData'
        ));
    }


}
