<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Retention;
use App\Models\Communicability;
use App\Models\Organisation;
use App\Models\ThesaurusConcept;

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
            'terms_count' => ThesaurusConcept::count(),
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
            'recent_terms' => ThesaurusConcept::latest()->take(5)->get(),
            'stats' => [
                'total_activities' => Activity::count(),
                'total_terms' => ThesaurusConcept::count(),
                'total_retentions' => Retention::count(),
                'total_communicabilities' => Communicability::count(),
                'total_organisations' => Organisation::count(),
            ]
        ];

        return view('tools.dashboard', $data);
    }
}
