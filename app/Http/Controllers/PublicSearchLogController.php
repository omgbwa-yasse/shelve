<?php

namespace App\Http\Controllers;

use App\Models\PublicSearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicSearchLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $searchLogs = PublicSearchLog::with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('public.search-logs.index', compact('searchLogs'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicSearchLog $searchLog)
    {
        return view('public.search-logs.show', compact('searchLog'));
    }

    /**
     * Display search statistics.
     */
    public function statistics()
    {
        $stats = [
            'total_searches' => PublicSearchLog::count(),
            'searches_today' => PublicSearchLog::whereDate('created_at', today())->count(),
            'searches_this_week' => PublicSearchLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'searches_this_month' => PublicSearchLog::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'most_common_terms' => PublicSearchLog::select('search_term')
                ->selectRaw('count(*) as count')
                ->groupBy('search_term')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'search_results' => PublicSearchLog::select('results_count')
                ->selectRaw('count(*) as count')
                ->groupBy('results_count')
                ->orderBy('results_count')
                ->get(),
        ];

        return view('public.search-logs.statistics', compact('stats'));
    }

    /**
     * Export search logs to CSV.
     */
    public function export()
    {
        $searchLogs = PublicSearchLog::with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="search-logs.csv"',
        ];

        $callback = function() use ($searchLogs) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, ['ID', 'User', 'Search Term', 'Results Count', 'Filters', 'Created At']);

            // Add data
            foreach ($searchLogs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user->name,
                    $log->search_term,
                    $log->results_count,
                    json_encode($log->filters),
                    $log->created_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear old search logs.
     */
    public function clearOldLogs()
    {
        $date = now()->subMonths(3);
        PublicSearchLog::where('created_at', '<', $date)->delete();

        return redirect()->route('public.search-logs.index')
            ->with('success', 'Old search logs cleared successfully.');
    }
}
