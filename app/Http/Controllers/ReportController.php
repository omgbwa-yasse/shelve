<?php

namespace App\Http\Controllers;

use App\Models\Accession;
use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Author;
use App\Models\Communicability;
use App\Models\Communication;
use App\Enums\CommunicationStatus;
use App\Models\Container;
use App\Models\Dolly;
use App\Models\DollyType;
use App\Models\Law;
use App\Models\LawArticle;
use App\Models\Mail;
use App\Models\MailAction;
use App\Models\MailAttachment;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\RecordAttachment;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\Retention;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\SlipStatus;
use App\Models\Sort;

use App\Models\ThesaurusConcept;
use App\Models\ThesaurusScheme;
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

        // Pour les mails envoyés, utilisation du status
        $sentMails = Mail::with(['action', 'sender', 'senderOrganisation'])
            ->where('status', 'sent')
            ->count();

        // Pour les mails reçus
        $receivedMails = Mail::with(['action', 'recipient', 'recipientOrganisation', 'attachments'])
            ->where('status', 'received')
            ->count();

        // Pour les mails en cours
        $inProgressMails = Mail::with(['action', 'sender', 'senderOrganisation'])
            ->where('status', 'in_progress')
            ->count();

        // Courriers par priorité avec la relation priority
        $mailsPriorityData = Mail::with(['action', 'priority'])
            ->select('priority_id', DB::raw('count(*) as count'))
            ->groupBy('priority_id')
            ->pluck('count', 'priority_id')
            ->toArray();

        $mailsPriorityLabels = MailPriority::whereIn('id', array_keys($mailsPriorityData))
            ->pluck('name', 'id')
            ->toArray();

        // Courriers par typologie avec la relation typology
        $mailsTypologyData = Mail::with(['action', 'typology'])
            ->select('typology_id', DB::raw('count(*) as count'))
            ->groupBy('typology_id')
            ->pluck('count', 'typology_id')
            ->toArray();

        $mailsTypologyLabels = MailTypology::whereIn('id', array_keys($mailsTypologyData))
            ->pluck('name', 'id')
            ->toArray();

        // Évolution du nombre de courriers
        $mailsEvolution = Mail::with(['action'])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $mailsEvolutionLabels = $mailsEvolution->pluck('date');
        $mailsEvolutionData = $mailsEvolution->pluck('count');

        // Top 5 des organisations expéditrices
        $topOrganisations = Mail::with(['action', 'senderOrganisation'])
            ->select('sender_organisation_id', DB::raw('count(*) as count'))
            ->groupBy('sender_organisation_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $topOrganisationsLabels = Organisation::whereIn('id', $topOrganisations->pluck('sender_organisation_id'))
            ->pluck('name', 'id')
            ->toArray();

        $topOrganisationsData = $topOrganisations->pluck('count')->toArray();

        // Temps moyen de traitement
        $averageProcessingTime = Mail::with(['action'])
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_time')
            ->value('avg_time');

        $processingTimeByPriority = Mail::with(['action', 'priority'])
            ->whereNotNull('updated_at')
            ->select('priority_id', DB::raw('AVG(DATEDIFF(updated_at, created_at)) as avg_time'))
            ->groupBy('priority_id')
            ->get();

        $processingTimeLabels = $mailsPriorityLabels;
        $processingTimeData = $processingTimeByPriority->pluck('avg_time')->toArray();

        // Statistiques des pièces jointes
        $totalAttachments = Mail::with(['attachments'])
            ->join('mail_attachment', 'mails.id', '=', 'mail_attachment.mail_id')
            ->count();

        $averageAttachmentSize = Mail::with(['attachments'])
                ->join('mail_attachment', 'mails.id', '=', 'mail_attachment.mail_id')
                ->join('attachments', 'mail_attachment.attachment_id', '=', 'attachments.id')
                ->avg('attachments.size') / 1024 / 1024; // Convertir en MB

        // Types de pièces jointes
        $attachmentTypeData = Mail::with(['attachments'])
            ->join('mail_attachment', 'mails.id', '=', 'mail_attachment.mail_id')
            ->join('attachments', 'mail_attachment.attachment_id', '=', 'attachments.id')
            ->select(
                DB::raw("SUBSTRING_INDEX(attachments.path, '.', -1) as extension"),
                DB::raw('count(*) as count')
            )
            ->groupBy(DB::raw("SUBSTRING_INDEX(attachments.path, '.', -1)"))
            ->pluck('count', 'extension')
            ->toArray();

        $attachmentTypeLabels = array_keys($attachmentTypeData);

        // Distribution mensuelle des courriers
        $monthlyDistribution = Mail::with(['action'])
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        $monthlyDistributionLabels = $monthlyDistribution->pluck('month')->map(function($month) {
            return Carbon::create()->month($month)->format('F');
        });

        $monthlyDistributionData = $monthlyDistribution->pluck('count');

        // Top 10 des auteurs
        $topAuthors = Mail::with(['authors'])
            ->join('mail_author', 'mails.id', '=', 'mail_author.mail_id')
            ->join('authors', 'mail_author.author_id', '=', 'authors.id')
            ->select('authors.id', 'authors.name', DB::raw('COUNT(*) as mail_count'))
            ->groupBy('authors.id', 'authors.name')
            ->orderByDesc('mail_count')
            ->limit(10)
            ->get();

        $topSendersLabels = $topAuthors->pluck('name');
        $topSendersData = $topAuthors->pluck('mail_count');

        // Actions sur les courriers
        $mailActions = Mail::with(['action'])
            ->join('mail_actions', 'mails.action_id', '=', 'mail_actions.id')
            ->select('mail_actions.name', DB::raw('count(*) as count'))
            ->groupBy('mail_actions.name')
            ->orderByDesc('count')
            ->get();

        $mailActionsLabels = $mailActions->pluck('name');
        $mailActionsData = $mailActions->pluck('count');

        return view('report.statistics.mails', compact(
            'totalMails', 'sentMails', 'receivedMails', 'inProgressMails',
            'mailsPriorityLabels', 'mailsPriorityData',
            'mailsTypologyLabels', 'mailsTypologyData',
            'mailsEvolutionLabels', 'mailsEvolutionData',
            'topOrganisationsLabels', 'topOrganisationsData',
            'averageProcessingTime', 'processingTimeLabels', 'processingTimeData',
            'totalAttachments', 'averageAttachmentSize', 'attachmentTypeLabels', 'attachmentTypeData',
            'monthlyDistributionLabels', 'monthlyDistributionData',
            'mailActionsLabels', 'mailActionsData',
            'topSendersLabels', 'topSendersData'
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
        $recordsWithTerms = Record::has('thesaurusConcepts')->count();
        $recordsWithAttachments = Record::has('attachments')->count();

//        $topOrganisations = Record::select('organisation_id', DB::raw('count(*) as count'))
//            ->groupBy('organisation_id')
//            ->orderByDesc('count')
//            ->limit(5)
//            ->get()
//            ->pluck('count', 'organisation_id')
//            ->toArray();

        $recordsWithChildren = Record::has('children')->count();
        $averageChildrenPerRecord = 0;

        // Préparer les données pour les graphiques
        $levelNames = RecordLevel::pluck('name', 'id')->toArray();
        $supportNames = RecordSupport::pluck('name', 'id')->toArray();
        $statusNames = RecordStatus::pluck('name', 'id')->toArray();
        $activityNames = Activity::pluck('name', 'id')->toArray();
//        $organisationNames = Organisation::whereIn('id', array_keys($topOrganisations))->pluck('name', 'id')->toArray();

        return view('report.statistics.repositories', compact(
            'totalRecords', 'recordsWithContainer', 'recordsWithoutContainer',
            'recordsByLevel', 'recordsBySupport', 'recordsByStatus',
            'recordsEvolutionLabels', 'recordsEvolutionData',
            'topActivities', 'totalAttachments', 'averageAttachmentSize',
            'attachmentTypes', 'monthlyDistributionLabels', 'monthlyDistributionData',
            'recordsWithAuthors', 'recordsWithTerms', 'recordsWithAttachments',
             'recordsWithChildren', 'averageChildrenPerRecord',
            'levelNames', 'supportNames', 'statusNames', 'activityNames',
        ));
    }

    public function statisticsCommunications()
    {
        // Statistiques générales
        $totalCommunications = Communication::count();
        $pendingCommunications = Communication::where('status', \App\Enums\CommunicationStatus::PENDING->value)->count(); // Utilisation de l'enum de statut
        $completedCommunications = Communication::whereNotNull('return_effective')->count();

        // Communications par statut
        $communicationsByStatus = Communication::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $statusNames = collect(CommunicationStatus::cases())->pluck('label', 'value')->toArray();

        // Évolution du nombre de communications
        $communicationsEvolution = Communication::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        $communicationsEvolutionLabels = $communicationsEvolution->pluck('date');
        $communicationsEvolutionData = $communicationsEvolution->pluck('count');

        // Top 5 des utilisateurs demandeurs
        $topUsers = Communication::select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
        $topUsersLabels = User::whereIn('id', $topUsers->pluck('user_id'))->pluck('name', 'id')->toArray();
        $topUsersData = $topUsers->pluck('count')->toArray();

        // Top 5 des organisations demandeuses
        $topOrganisations = Communication::select('user_organisation_id', DB::raw('count(*) as count'))
            ->groupBy('user_organisation_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
        $topOrganisationsLabels = Organisation::whereIn('id', $topOrganisations->pluck('user_organisation_id'))->pluck('name', 'id')->toArray();
        $topOrganisationsData = $topOrganisations->pluck('count')->toArray();

        // Temps moyen de retour (corrigé)
        $averageReturnTime = Communication::whereNotNull('return_effective')
            ->whereRaw('return_effective > created_at')
            ->selectRaw('AVG(DATEDIFF(return_effective, created_at)) as avg_time')
            ->value('avg_time');

        $averageReturnTime = $averageReturnTime ? number_format($averageReturnTime, 1) : 'N/A';

        // Distribution mensuelle des communications
        $monthlyDistribution = Communication::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();
        $monthlyDistributionLabels = $monthlyDistribution->pluck('month')->map(function($month) {
            return Carbon::create()->month($month)->format('F');
        })->toArray();
        $monthlyDistributionData = $monthlyDistribution->pluck('count')->toArray();

        return view('report.statistics.communications', compact(
            'totalCommunications', 'pendingCommunications', 'completedCommunications',
            'communicationsByStatus', 'statusNames',
            'communicationsEvolutionLabels', 'communicationsEvolutionData',
            'topUsersLabels', 'topUsersData',
            'topOrganisationsLabels', 'topOrganisationsData',
            'averageReturnTime',
            'monthlyDistributionLabels', 'monthlyDistributionData'
        ));
    }

    public function statisticsTransferrings()
    {
        // Statistiques générales sur les bordereaux
        $totalSlips = Slip::count();
        $pendingSlips = Slip::where('slip_status_id', 1)->count(); // Supposons que 1 est le statut "en attente"
        $approvedSlips = Slip::where('is_approved', true)->count();
        $integratedSlips = Slip::where('is_integrated', true)->count();

        // Bordereaux par statut
        $slipsByStatus = Slip::select('slip_status_id', DB::raw('count(*) as count'))
            ->groupBy('slip_status_id')
            ->pluck('count', 'slip_status_id')
            ->toArray();
        $statusNames = SlipStatus::pluck('name', 'id')->toArray();

        // Évolution du nombre de bordereaux
        $slipsEvolution = Slip::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        $slipsEvolutionLabels = $slipsEvolution->pluck('date');
        $slipsEvolutionData = $slipsEvolution->pluck('count');

        // Top 5 des organisations de transfert
        $topOrganisations = Slip::select('user_organisation_id', DB::raw('count(*) as count'))
            ->groupBy('user_organisation_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
        $topOrganisationsLabels = Organisation::whereIn('id', $topOrganisations->pluck('user_organisation_id'))->pluck('name', 'id')->toArray();
        $topOrganisationsData = $topOrganisations->pluck('count')->toArray();

        // Statistiques sur les enregistrements de bordereaux
        $totalSlipRecords = SlipRecord::count();
        $averageRecordsPerSlip = DB::table(function ($query) {
            $query->select('slip_id', DB::raw('count(*) as record_count'))
                ->from('slip_records')
                ->groupBy('slip_id');
        }, 'subquery')->avg('record_count');

        // Distribution mensuelle des bordereaux
        $monthlyDistribution = Slip::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();
        $monthlyDistributionLabels = $monthlyDistribution->pluck('month')->map(function($month) {
            return Carbon::create()->month($month)->format('F');
        })->toArray();
        $monthlyDistributionData = $monthlyDistribution->pluck('count')->toArray();

        return view('report.statistics.transferrings', compact(
            'totalSlips', 'pendingSlips', 'approvedSlips', 'integratedSlips',
            'slipsByStatus', 'statusNames',
            'slipsEvolutionLabels', 'slipsEvolutionData',
            'topOrganisationsLabels', 'topOrganisationsData',
            'totalSlipRecords', 'averageRecordsPerSlip',
            'monthlyDistributionLabels', 'monthlyDistributionData'
        ));
    }


    public function statisticsDeposits()
    {
        // Statistiques générales
        $totalBuildings = \App\Models\Building::count();
        $totalFloors = \App\Models\Floor::count();
        $totalRooms = \App\Models\Room::count();
        $totalShelves = \App\Models\Shelf::count();
        $totalContainers = \App\Models\Container::count();

        // Distribution des conteneurs par statut
        $containersByStatus = \App\Models\Container::select('status_id', \DB::raw('count(*) as count'))
            ->groupBy('status_id')
            ->pluck('count', 'status_id')
            ->toArray();

        // Top 5 des bâtiments par nombre de conteneurs
        $topBuildings = \App\Models\Building::withCount(['floors' => function ($query) {
            $query->withCount(['rooms' => function ($query) {
                $query->withCount(['shelves' => function ($query) {
                    $query->withCount('containers');
                }]);
            }]);
        }])
            ->get()
            ->sortByDesc(function ($building) {
                return $building->floors->sum(function ($floor) {
                    return $floor->rooms->sum(function ($room) {
                        return $room->shelves->sum('containers_count');
                    });
                });
            })
            ->take(5);

        // Utilisation moyenne des étagères (nombre de conteneurs par étagère)
        $averageContainersPerShelf = \DB::table('containers')
            ->selectRaw('COUNT(*) / COUNT(DISTINCT shelve_id) as average_containers')
            ->value('average_containers');

        // Distribution des salles par type
        $roomsByType = \App\Models\Room::select('type_id', \DB::raw('count(*) as count'))
            ->groupBy('type_id')
            ->pluck('count', 'type_id')
            ->toArray();

        // Évolution du nombre de conteneurs au fil du temps
        $containerEvolution = \App\Models\Container::select(\DB::raw('DATE(created_at) as date'), \DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Capacité totale vs utilisation réelle
        $totalCapacity = \App\Models\Shelf::sum('shelf_length');
        $usedCapacity = \App\Models\Container::join('container_properties', 'containers.property_id', '=', 'container_properties.id')
            ->sum('container_properties.length');

        // Top 5 des organisations créatrices de conteneurs
        $topOrganisations = \App\Models\Container::select('creator_organisation_id', \DB::raw('count(*) as count'))
            ->groupBy('creator_organisation_id')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        return view('report.statistics.deposits', compact(
            'totalBuildings', 'totalFloors', 'totalRooms', 'totalShelves', 'totalContainers',
            'containersByStatus', 'topBuildings', 'averageContainersPerShelf',
            'roomsByType', 'containerEvolution', 'totalCapacity', 'usedCapacity', 'topOrganisations'
        ));
    }

    public function statisticsTools()
    {
        // Plan de classement (Activities)
        $totalActivities = Activity::count();
        $topLevelActivities = Activity::whereNull('parent_id')->count();
        $activitiesWithCommunicability = Activity::whereNotNull('communicability_id')->count();

        // Référentiel de conservation (Retentions)
        $totalRetentions = Retention::count();
        $averageRetentionDuration = Retention::avg('duration');
        $retentionsBySort = Retention::select('sort_id', DB::raw('count(*) as count'))
            ->groupBy('sort_id')
            ->pluck('count', 'sort_id')
            ->toArray();
        $sortNames = Sort::pluck('name', 'id')->toArray();

        // Lois et articles
        $totalLaws = Law::count();
        $totalLawArticles = LawArticle::count();

        // Communicabilités
        $totalCommunicabilities = Communicability::count();
        $averageCommunicabilityDuration = Communicability::avg('duration');

        // Organigramme (Organisations)
        $totalOrganisations = Organisation::count();
        $topLevelOrganisations = Organisation::whereNull('parent_id')->count();

        // Thésaurus (Concepts)
        $totalTerms = ThesaurusConcept::count();
        // Group by scheme as a proxy for category
        $termsByCategory = ThesaurusConcept::select('scheme_id', DB::raw('count(*) as count'))
            ->groupBy('scheme_id')
            ->pluck('count', 'scheme_id')
            ->toArray();
        $categoryNames = \App\Models\ThesaurusScheme::whereIn('id', array_keys($termsByCategory))
            ->pluck('title', 'id')
            ->toArray();

        // Group by language via preferred labels
        $termsByLanguage = \App\Models\ThesaurusLabel::select('language', DB::raw('count(*) as count'))
            ->where('type', 'prefLabel')
            ->groupBy('language')
            ->pluck('count', 'language')
            ->toArray();
        $languageNames = $termsByLanguage ? array_combine(array_keys($termsByLanguage), array_keys($termsByLanguage)) : [];

        return view('report.statistics.tools', compact(
            'totalActivities', 'topLevelActivities', 'activitiesWithCommunicability',
            'totalRetentions', 'averageRetentionDuration', 'retentionsBySort', 'sortNames',
            'totalLaws', 'totalLawArticles',
            'totalCommunicabilities', 'averageCommunicabilityDuration',
            'totalOrganisations', 'topLevelOrganisations',
            'totalTerms', 'termsByCategory', 'categoryNames', 'termsByLanguage', 'languageNames'
        ));
    }

    public function statisticsDollies()
    {
        // Statistiques générales
        $totalDollies = Dolly::count();

        // Dollies par catégorie
        $dolliesByType = Dolly::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get();
        $dollyTypeLabels = $dolliesByType->pluck('category')->mapWithKeys(function ($item) {
            return [$item => ucfirst($item)];
        })->toArray();
        $dollyTypeData = $dolliesByType->pluck('count', 'category')->toArray();

        // Évolution du nombre de dollies
        $dolliesEvolution = Dolly::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
        $dolliesEvolutionLabels = $dolliesEvolution->pluck('date');
        $dolliesEvolutionData = $dolliesEvolution->pluck('count');

        // Statistiques des éléments dans les dollies
        $totalMails = DB::table('dolly_mails')->count();
        $totalRecords = DB::table('dolly_records')->count();
        $totalCommunications = DB::table('dolly_communications')->count();
        $totalSlips = DB::table('dolly_slips')->count();
        $totalSlipRecords = DB::table('dolly_slip_records')->count();
        $totalBuildings = DB::table('dolly_buildings')->count();
        $totalRooms = DB::table('dolly_rooms')->count();
        $totalShelves = DB::table('dolly_shelves')->count();
        $totalContainers = DB::table('dolly_containers')->count();

        $totalItems = $totalMails + $totalRecords + $totalCommunications + $totalSlips +
            $totalSlipRecords + $totalBuildings + $totalRooms + $totalShelves + $totalContainers;

        $averageItemsPerDolly = $totalDollies > 0 ? $totalItems / $totalDollies : 0;

        // Types d'éléments dans les dollies
        $itemTypes = [
            'Mails' => $totalMails,
            'Records' => $totalRecords,
            'Communications' => $totalCommunications,
            'Slips' => $totalSlips,
            'Slip Records' => $totalSlipRecords,
            'Buildings' => $totalBuildings,
            'Rooms' => $totalRooms,
            'Shelves' => $totalShelves,
            'Containers' => $totalContainers
        ];

        // Distribution mensuelle des créations de dollies
        $monthlyDistribution = Dolly::select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();
        $monthlyDistributionLabels = $monthlyDistribution->pluck('month')->map(function($month) {
            return date('F', mktime(0, 0, 0, $month, 1));
        });
        $monthlyDistributionData = $monthlyDistribution->pluck('count');

        return view('report.statistics.dollies', compact(
            'totalDollies',
            'dollyTypeLabels', 'dollyTypeData',
            'dolliesEvolutionLabels', 'dolliesEvolutionData',
            'totalItems', 'averageItemsPerDolly',
            'itemTypes',
            'monthlyDistributionLabels', 'monthlyDistributionData'
        ));
    }
    public function dashboard()
    {
        // Communications
        $totalCommunications = Communication::count();
        $pendingCommunications = Communication::where('status', \App\Enums\CommunicationStatus::PENDING->value)->count(); // Using the status enum

        // Dollies
        $totalDollies = Dolly::count();
        $dolliesByType = Dolly::query()
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category');

        // Mails
        $totalMails = Mail::count();
        $mailsByPriority = Mail::query()
            ->select('priority_id', DB::raw('count(*) as count'))
            ->groupBy('priority_id')
            ->pluck('count', 'priority_id');

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
            ->select('priority_id', DB::raw('count(*) as count'))
            ->groupBy('priority_id')
            ->pluck('priority_id');

        $mailsData = Mail::query()
            ->select('priority_id', DB::raw('count(*) as count'))
            ->groupBy('priority_id')
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
