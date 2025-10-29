<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
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

        // Log search if user is authenticated
        if (Auth::guard('public')->check()) {
            $this->logSearch($validated);
        }

        // Perform search logic here
        $results = collect(); // Placeholder for search results
        $totalResults = 0;

        // In a real implementation, this would search through records
        // using Elasticsearch, database full-text search, or similar

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
            return redirect()->route('opac.login');
        }

        // Get user's search history
        $searchHistory = collect(); // Placeholder

        return view('opac.search.history', compact('searchHistory'));
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
     * Log search query for statistics and history
     */
    private function logSearch(array $searchData)
    {
        $user = Auth::guard('public')->user();

        // Implementation for logging searches
        // Could be stored in a search_logs table

        // Example:
        // PublicSearchLog::create([
        //     'public_user_id' => $user->id,
        //     'query' => json_encode($searchData),
        //     'ip_address' => request()->ip(),
        //     'user_agent' => request()->userAgent(),
        //     'created_at' => now(),
        // ]);
    }
}
