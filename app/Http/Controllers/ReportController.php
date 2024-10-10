<?php

namespace App\Http\Controllers;

use App\Models\Accession;
use App\Models\Activity;
use App\Models\Communicability;
use App\Models\Communication;
use App\Models\Container;
use App\Models\Dolly;
use App\Models\Mail;
use App\Models\Organisation;
use App\Models\Record;
use App\Models\Slip;
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
    public function myTasks()
    {
        // Logique pour afficher les tâches de l'utilisateur connecté
        $tasks = Task::where('user_id', auth()->id())->get();
        return view('report.my-tasks', compact('tasks'));
    }

    public function allTasks()
    {
        // Logique pour afficher toutes les tâches
        $tasks = Task::all();
        return view('report.all-tasks', compact('tasks'));
    }

    public function createTask()
    {
        // Logique pour créer une nouvelle tâche
        return view('report.create-task');
    }

    public function statisticsMails()
    {
        // Logique pour afficher les statistiques du module Courrier
        return view('report.statistics.mails');
    }

    public function statisticsRepositories()
    {
        // Logique pour afficher les statistiques du module Répertoire
        return view('report.statistics.repositories');
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
