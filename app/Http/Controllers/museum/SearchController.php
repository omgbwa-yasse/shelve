<?php

namespace App\Http\Controllers\Museum;

use App\Http\Controllers\Controller;
use App\Models\RecordArtifact;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Display search interface.
     */
    public function index()
    {
        return view('museum.search.index');
    }

    /**
     * Perform search.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        $results = RecordArtifact::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('author', 'like', "%{$query}%")
                    ->orWhere('material', 'like', "%{$query}%");
            })
            ->paginate(20);

        return view('museum.search.results', compact('results', 'query'));
    }

    /**
     * Display advanced search.
     */
    public function advanced()
    {
        // Options pour les filtres
        $categories = RecordArtifact::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');

        $materials = RecordArtifact::select('material')
            ->distinct()
            ->whereNotNull('material')
            ->orderBy('material')
            ->pluck('material');

        $origins = RecordArtifact::select('origin')
            ->distinct()
            ->whereNotNull('origin')
            ->orderBy('origin')
            ->pluck('origin');

        $conservationStates = RecordArtifact::select('conservation_state')
            ->distinct()
            ->whereNotNull('conservation_state')
            ->orderBy('conservation_state')
            ->pluck('conservation_state');

        return view('museum.search.advanced', compact(
            'categories',
            'materials',
            'origins',
            'conservationStates'
        ));
    }

    /**
     * Perform advanced search.
     */
    public function advancedSearch(Request $request)
    {
        $query = RecordArtifact::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        if ($request->filled('code')) {
            $query->where('code', 'like', "%{$request->code}%");
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('material')) {
            $query->where('material', $request->material);
        }

        if ($request->filled('origin')) {
            $query->where('origin', 'like', "%{$request->origin}%");
        }

        if ($request->filled('author')) {
            $query->where('author', 'like', "%{$request->author}%");
        }

        if ($request->filled('period')) {
            $query->where('period', 'like', "%{$request->period}%");
        }

        if ($request->filled('date_start')) {
            $query->where('date_start', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->where('date_end', '<=', $request->date_end);
        }

        if ($request->filled('conservation_state')) {
            $query->where('conservation_state', $request->conservation_state);
        }

        if ($request->filled('location')) {
            $query->where(function ($q) use ($request) {
                $q->where('current_location', 'like', "%{$request->location}%")
                    ->orWhere('storage_location', 'like', "%{$request->location}%");
            });
        }

        if ($request->filled('is_on_display')) {
            $query->where('is_on_display', $request->is_on_display === '1');
        }

        if ($request->filled('is_on_loan')) {
            $query->where('is_on_loan', $request->is_on_loan === '1');
        }

        $results = $query->orderBy('code')->paginate(20);

        return view('museum.search.results', compact('results'));
    }
}
