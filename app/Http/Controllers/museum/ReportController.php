<?php

namespace App\Http\Controllers\Museum;

use App\Http\Controllers\Controller;
use App\Models\RecordArtifact;
use App\Models\RecordArtifactExhibition;
use App\Models\RecordArtifactLoan;
use App\Models\RecordArtifactConditionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    /**
     * Display reports dashboard.
     */
    public function index()
    {
        $availableReports = [
            [
                'name' => 'Rapport de collection',
                'description' => 'Inventaire complet des artefacts',
                'route' => 'museum.reports.collection',
            ],
            [
                'name' => 'Rapport de conservation',
                'description' => 'État de conservation des pièces',
                'route' => 'museum.reports.conservation',
            ],
            [
                'name' => 'Rapport d\'expositions',
                'description' => 'Historique et statistiques d\'expositions',
                'route' => 'museum.reports.exhibitions',
            ],
            [
                'name' => 'Rapport de valorisation',
                'description' => 'Évaluation financière de la collection',
                'route' => 'museum.reports.valuation',
            ],
            [
                'name' => 'Statistiques générales',
                'description' => 'Vue d\'ensemble du musée',
                'route' => 'museum.reports.statistics',
            ],
        ];

        return view('museum.reports.index', compact('availableReports'));
    }

    /**
     * Generate collection report.
     */
    public function collection(Request $request)
    {
        $artifacts = RecordArtifact::orderBy('code')->get();

        $stats = [
            'total_artifacts' => $artifacts->count(),
            'on_display' => $artifacts->where('is_on_display', true)->count(),
            'on_loan' => $artifacts->where('is_on_loan', true)->count(),
            'in_storage' => $artifacts->where('is_on_display', false)->where('is_on_loan', false)->count(),
            'by_category' => $artifacts->groupBy('category')->map->count(),
            'by_material' => $artifacts->groupBy('material')->map->count(),
        ];

        if ($request->get('format') === 'csv') {
            return $this->exportCollectionCsv($artifacts);
        }

        return view('museum.reports.collection', compact('artifacts', 'stats'));
    }

    /**
     * Generate conservation report.
     */
    public function conservation()
    {
        $artifacts = RecordArtifact::with('conditionReports')
            ->orderBy('code')
            ->get();

        $stats = [
            'by_state' => $artifacts->groupBy('conservation_state')->map->count(),
            'total_reports' => RecordArtifactConditionReport::count(),
            'artifacts_needing_attention' => $artifacts->whereIn('conservation_state', ['poor', 'damaged'])->count(),
        ];

        return view('museum.reports.conservation', compact('artifacts', 'stats'));
    }

    /**
     * Generate exhibitions report.
     */
    public function exhibitions()
    {
        $exhibitions = RecordArtifactExhibition::with('artifact')
            ->orderBy('start_date', 'desc')
            ->get();

        $stats = [
            'total_exhibitions' => $exhibitions->count(),
            'current' => $exhibitions->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->count(),
            'past' => $exhibitions->where('end_date', '<', now())->count(),
            'upcoming' => $exhibitions->where('start_date', '>', now())->count(),
            'total_visitors' => $exhibitions->sum('visitor_count'),
            'avg_duration' => $exhibitions->avg(function ($exhibition) {
                return now()->parse($exhibition->start_date)->diffInDays($exhibition->end_date);
            }),
        ];

        return view('museum.reports.exhibitions', compact('exhibitions', 'stats'));
    }

    /**
     * Display valuation report.
     */
    public function valuation()
    {
        $artifacts = RecordArtifact::whereNotNull('estimated_value')
            ->orWhereNotNull('insurance_value')
            ->orderBy('estimated_value', 'desc')
            ->get();

        $stats = [
            'total_estimated_value' => $artifacts->sum('estimated_value'),
            'total_insurance_value' => $artifacts->sum('insurance_value'),
            'avg_estimated_value' => $artifacts->avg('estimated_value'),
            'most_valuable' => $artifacts->sortByDesc('estimated_value')->take(10),
            'by_category' => $artifacts->groupBy('category')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total_value' => $items->sum('estimated_value'),
                    'avg_value' => $items->avg('estimated_value'),
                ];
            }),
        ];

        return view('museum.reports.valuation', compact('artifacts', 'stats'));
    }

    /**
     * Display statistics report.
     */
    public function statistics()
    {
        $stats = [
            // Collection
            'collection' => [
                'total_artifacts' => RecordArtifact::count(),
                'by_category' => RecordArtifact::selectRaw('category, COUNT(*) as count')
                    ->groupBy('category')
                    ->orderBy('count', 'desc')
                    ->get(),
                'by_origin' => RecordArtifact::selectRaw('origin, COUNT(*) as count')
                    ->whereNotNull('origin')
                    ->groupBy('origin')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get(),
                'by_period' => RecordArtifact::selectRaw('period, COUNT(*) as count')
                    ->whereNotNull('period')
                    ->groupBy('period')
                    ->orderBy('count', 'desc')
                    ->get(),
            ],

            // Conservation
            'conservation' => [
                'by_state' => RecordArtifact::selectRaw('conservation_state, COUNT(*) as count')
                    ->groupBy('conservation_state')
                    ->get(),
                'total_reports' => RecordArtifactConditionReport::count(),
                'recent_reports' => RecordArtifactConditionReport::where('report_date', '>=', now()->subMonths(6))
                    ->count(),
            ],

            // Expositions
            'exhibitions' => [
                'total' => RecordArtifactExhibition::count(),
                'current' => RecordArtifactExhibition::where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->count(),
                'total_visitors' => RecordArtifactExhibition::sum('visitor_count'),
                'by_year' => RecordArtifactExhibition::selectRaw('YEAR(start_date) as year, COUNT(*) as count')
                    ->groupBy('year')
                    ->orderBy('year', 'desc')
                    ->get(),
            ],

            // Prêts
            'loans' => [
                'total' => RecordArtifactLoan::count(),
                'active' => RecordArtifact::where('is_on_loan', true)->count(),
            ],

            // Acquisitions
            'acquisitions' => [
                'by_year' => RecordArtifact::selectRaw('YEAR(acquisition_date) as year, COUNT(*) as count')
                    ->whereNotNull('acquisition_date')
                    ->groupBy('year')
                    ->orderBy('year', 'desc')
                    ->get(),
                'by_method' => RecordArtifact::selectRaw('acquisition_method, COUNT(*) as count')
                    ->whereNotNull('acquisition_method')
                    ->groupBy('acquisition_method')
                    ->get(),
            ],
        ];

        return view('museum.reports.statistics', compact('stats'));
    }

    /**
     * Export collection to CSV.
     */
    protected function exportCollectionCsv($artifacts)
    {
        $filename = 'museum_collection_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($artifacts) {
            $file = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($file, [
                'Code', 'Nom', 'Catégorie', 'Matériau', 'Auteur',
                'Origine', 'Période', 'État conservation', 'Emplacement',
                'En exposition', 'En prêt', 'Valeur estimée'
            ]);

            // Données
            foreach ($artifacts as $artifact) {
                fputcsv($file, [
                    $artifact->code,
                    $artifact->name,
                    $artifact->category,
                    $artifact->material,
                    $artifact->author,
                    $artifact->origin,
                    $artifact->period,
                    $artifact->conservation_state,
                    $artifact->current_location,
                    $artifact->is_on_display ? 'Oui' : 'Non',
                    $artifact->is_on_loan ? 'Oui' : 'Non',
                    $artifact->estimated_value,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
