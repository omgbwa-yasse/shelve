<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Term;
use App\Models\Retention;
use App\Models\Communicability;
use App\Models\Organisation;

class ToolsController extends Controller
{
    /**
     * Page d'accueil des outils
     */
    public function index()
    {
        // Récupérer quelques statistiques pour la page d'accueil
        $stats = [
            'activities_count' => Activity::count(),
            'terms_count' => Term::count(),
            'retentions_count' => Retention::count(),
            'communicabilities_count' => Communicability::count(),
            'organisations_count' => Organisation::count(),
        ];

        return view('tools.index', compact('stats'));
    }

    /**
     * Dashboard avec statistiques des outils
     */
    public function dashboard()
    {
        $data = [
            'recent_activities' => Activity::latest()->take(5)->get(),
            'recent_terms' => Term::latest()->take(5)->get(),
            'stats' => [
                'total_activities' => Activity::count(),
                'total_terms' => Term::count(),
                'total_retentions' => Retention::count(),
                'total_communicabilities' => Communicability::count(),
                'total_organisations' => Organisation::count(),
            ]
        ];

        return view('tools.dashboard', $data);
    }
}
