<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicSearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * OPAC Search Controller - Advanced search functionality with history
 */
class SearchController extends Controller
{
    /**
     * Advanced search form
     */
    public function index()
    {
        // Get search history for authenticated users
        $searchHistory = [];
        if (Auth::guard('public')->check()) {
            // Get recent searches (if we implement search logging)
            $searchHistory = collect(); // Placeholder
        }

        return view('opac.search.index', compact('searchHistory'));
    }

    /**
     * Perform search and return results
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:50',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'type' => 'nullable|string',
            'category' => 'nullable|string',
            'sort' => 'nullable|in:relevance,title,author,date_asc,date_desc',
        ]);

        // Perform search logic here
        $results = collect(); // Placeholder for search results
        $totalResults = 0;

        // In a real implementation, this would search through records
        // using Elasticsearch, database full-text search, or similar

        // Log search if user is authenticated (after getting results)
        if (Auth::guard('public')->check()) {
            $this->logSearch($validated, $totalResults);
        }

        return view('opac.search.results', compact('results', 'totalResults', 'validated'));
    }

    /**
     * Get search suggestions (AJAX)
     */
    public function suggestions(Request $request)
    {
        $term = $request->get('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        // Get suggestions from various sources
        $suggestions = [
            'titles' => [],
            'authors' => [],
            'subjects' => [],
        ];

        // In real implementation, query the database for suggestions

        return response()->json($suggestions);
    }

    /**
     * Show search history for authenticated users
     */
    public function history()
    {
        $user = Auth::guard('public')->user();

        if (!$user) {
            return redirect()->route('opac.login')
                ->with('message', 'Vous devez être connecté pour accéder à votre historique de recherche.');
        }

        // Get user's search history ordered by most recent
        $searchHistory = PublicSearchLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get search statistics
        $totalSearches = PublicSearchLog::where('user_id', $user->id)->count();
        $recentSearches = PublicSearchLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // Get popular search terms
        $popularTerms = PublicSearchLog::where('user_id', $user->id)
            ->select('search_term')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('search_term')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('opac.search.history', compact(
            'searchHistory',
            'totalSearches',
            'recentSearches',
            'popularTerms'
        ));
    }

    /**
     * Save a search to favorites
     */
    public function saveSearch(Request $request)
    {
        $user = Auth::guard('public')->user();

        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $validated = $request->validate([
            'query' => 'required|string',
            'filters' => 'nullable|array',
            'name' => 'required|string|max:255',
        ]);

        // Save search logic here

        return response()->json(['message' => 'Search saved successfully']);
    }

    /**
     * Delete a specific search from history
     */
    public function deleteSearch($searchId)
    {
        $user = Auth::guard('public')->user();

        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $search = PublicSearchLog::where('id', $searchId)
            ->where('user_id', $user->id)
            ->first();

        if (!$search) {
            return response()->json(['error' => 'Search not found'], 404);
        }

        $search->delete();

        return response()->json(['success' => true, 'message' => 'Recherche supprimée avec succès']);
    }

    /**
     * Clear entire search history for the user
     */
    public function clearHistory()
    {
        $user = Auth::guard('public')->user();

        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $deletedCount = PublicSearchLog::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => "Historique supprimé avec succès ({$deletedCount} recherche(s) supprimée(s))"
        ]);
    }

    /**
     * API search for AJAX autocomplete
     */
    public function apiSearch(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Get limited results for autocomplete
        $results = collect(); // Placeholder - in real implementation would search records

        // For now, return empty results until proper search implementation
        return response()->json($results);
    }

    /**
     * Log search query for statistics and history
     */
    private function logSearch(array $searchData, int $resultCount = 0)
    {
        $user = Auth::guard('public')->user();

        if (!$user) {
            return;
        }

        // Construire le terme de recherche principal
        $searchTerm = $searchData['q'] ?? '';
        if (empty($searchTerm) && !empty($searchData['title'])) {
            $searchTerm = $searchData['title'];
        }
        if (empty($searchTerm) && !empty($searchData['author'])) {
            $searchTerm = $searchData['author'];
        }
        if (empty($searchTerm)) {
            $searchTerm = 'Recherche avancée';
        }

        // Filtrer les données de recherche pour les filtres
        $filters = array_filter($searchData, function($value, $key) {
            return !empty($value) && $key !== 'q';
        }, ARRAY_FILTER_USE_BOTH);

        // Créer l'entrée dans l'historique
        PublicSearchLog::create([
            'user_id' => $user->id,
            'search_term' => $searchTerm,
            'filters' => !empty($filters) ? json_encode($filters) : null,
            'results_count' => $resultCount,
        ]);
    }
}
