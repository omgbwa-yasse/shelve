<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicSearchLog;
use App\Models\RecordBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

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
        $searchHistory = collect();
        if (Auth::guard('public')->check()) {
            $user = Auth::guard('public')->user();
            $searchHistory = PublicSearchLog::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
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
            'sort' => 'nullable|in:relevance,title_asc,title_desc,author_asc,date_asc,date_desc',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = $validated['q'] ?? null;
        $type = $validated['type'] ?? null;
        $results = collect();

        // Search Books
        if (!$type || $type === 'book') {
            $bookQuery = RecordBook::query()->with(['authors', 'publisher']);

            if ($query) {
                $bookQuery->where(function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('subtitle', 'like', "%{$query}%")
                      ->orWhere('isbn', 'like', "%{$query}%")
                      ->orWhereHas('authors', function($q) use ($query) {
                          $q->where('last_name', 'like', "%{$query}%")
                            ->orWhere('first_name', 'like', "%{$query}%");
                      });
                });
            }

            if (!empty($validated['title'])) {
                $bookQuery->where('title', 'like', "%{$validated['title']}%");
            }

            if (!empty($validated['author'])) {
                $bookQuery->whereHas('authors', function($q) use ($validated) {
                    $q->where('last_name', 'like', "%{$validated['author']}%")
                      ->orWhere('first_name', 'like', "%{$validated['author']}%");
                });
            }

            if (!empty($validated['isbn'])) {
                $bookQuery->where('isbn', 'like', "%{$validated['isbn']}%");
            }

            if (!empty($validated['language'])) {
                $bookQuery->whereHas('language', function($q) use ($validated) {
                    $q->where('code', $validated['language'])
                      ->orWhere('iso_639_1', $validated['language']);
                });
            }

            if (!empty($validated['subject'])) {
                $bookQuery->whereHas('subjects', function($q) use ($validated) {
                    $q->where('name', 'like', "%{$validated['subject']}%");
                });
            }

            if (!empty($validated['date_from'])) {
                $bookQuery->where('publication_year', '>=', substr($validated['date_from'], 0, 4));
            }

            if (!empty($validated['date_to'])) {
                $bookQuery->where('publication_year', '<=', substr($validated['date_to'], 0, 4));
            }

            $books = $bookQuery->get()->map(function($book) {
                return (object) [
                    'id' => $book->id,
                    'type' => 'book',
                    'title' => $book->full_title,
                    'description' => Str::limit($book->description, 150),
                    'author' => $book->authors_string,
                    'date' => $book->publication_year,
                    'image' => null, // Add image logic if available
                    'url' => route('opac.books.show', $book->id),
                    'model' => $book
                ];
            });

            $results = $results->merge($books);
        }

        // Recherche « Dossiers/Documents numériques » retirée le 2026-08-06
        // (RecordDigitalFolder/RecordDigitalDocument supprimés — voir App\Models\Record).

        // Sorting
        $sort = $validated['sort'] ?? 'relevance';
        if ($sort === 'title_asc') {
            $results = $results->sortBy('title');
        } elseif ($sort === 'title_desc') {
            $results = $results->sortByDesc('title');
        } elseif ($sort === 'date_desc') {
            $results = $results->sortByDesc('date');
        } elseif ($sort === 'date_asc') {
            $results = $results->sortBy('date');
        }
        // Relevance is default (no sort needed as we just appended results)

        // Pagination
        $page = $request->get('page', 1);
        $perPage = 15;
        $totalResults = $results->count();

        $paginatedResults = new LengthAwarePaginator(
            $results->forPage($page, $perPage),
            $totalResults,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Log search if user is authenticated (after getting results)
        if (Auth::guard('public')->check()) {
            $this->logSearch($validated, $totalResults);
        }

        return view('opac.search.results', [
            'results' => $paginatedResults,
            'totalResults' => $totalResults,
            'validated' => $validated
        ]);
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
