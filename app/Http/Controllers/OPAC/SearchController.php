<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicNews;
use App\Models\PublicEvent;
use App\Models\PublicRecord;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalFolder;
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

        // Get latest news and upcoming events for the landing page
        $latestNews = PublicNews::orderBy('published_at', 'desc')->limit(3)->get();
        $upcomingEvents = PublicEvent::where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->limit(3)
            ->get();

        return view('opac.search.index', compact('searchHistory', 'latestNews', 'upcomingEvents'));
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

        // Search Public Records (replacing non-existent RecordBook)
        if (!$type || $type === 'book' || $type === 'record') {
            $recordQuery = PublicRecord::available()->with(['record.authors', 'publisher']);

            if ($query) {
                $recordQuery->searchContent($query);
            }

            if (!empty($validated['title'])) {
                $recordQuery->whereHas('record', function($q) use ($validated) {
                    $q->where('name', 'like', "%{$validated['title']}%");
                });
            }

            if (!empty($validated['author'])) {
                $recordQuery->whereHas('record.authors', function($q) use ($validated) {
                    $q->where('last_name', 'like', "%{$validated['author']}%")
                      ->orWhere('first_name', 'like', "%{$validated['author']}%");
                });
            }

            if (!empty($validated['subject'])) {
                $recordQuery->whereHas('record.keywords', function($q) use ($validated) {
                    $q->where('name', 'like', "%{$validated['subject']}%");
                });
            }

            $records = $recordQuery->get()->map(function($publicRecord) {
                return (object) [
                    'id' => $publicRecord->id,
                    'type' => 'record',
                    'title' => $publicRecord->title,
                    'description' => Str::limit($publicRecord->description, 150),
                    'author' => $publicRecord->authors,
                    'date' => $publicRecord->publication_year,
                    'image' => $publicRecord->cover_image,
                    'url' => route('opac.records.show', $publicRecord->id),
                    'model' => $publicRecord
                ];
            });

            $results = $results->merge($records);
        }

        // Search Digital Folders
        if (!$type || $type === 'archive') {
            $folderQuery = RecordDigitalFolder::query();

            if ($query) {
                $folderQuery->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                });
            }

            if (!empty($validated['title'])) {
                $folderQuery->where('name', 'like', "%{$validated['title']}%");
            }

            if (!empty($validated['author'])) {
                $folderQuery->whereHas('creator', function($q) use ($validated) {
                    $q->where('name', 'like', "%{$validated['author']}%");
                });
            }

            if (!empty($validated['subject'])) {
                $folderQuery->whereHas('keywords', function($q) use ($validated) {
                    $q->where('name', 'like', "%{$validated['subject']}%");
                });
            }

            $folders = $folderQuery->get()->map(function($folder) {
                return (object) [
                    'id' => $folder->id,
                    'type' => 'folder',
                    'title' => $folder->name,
                    'description' => Str::limit($folder->description, 150),
                    'author' => $folder->creator ? $folder->creator->name : null,
                    'date' => $folder->created_at->format('Y'),
                    'image' => null,
                    'url' => route('opac.digital.folders.show', $folder->id),
                    'model' => $folder
                ];
            });

            $results = $results->merge($folders);
        }

        // Search Digital Documents
        if (!$type || in_array($type, ['article', 'report', 'thesis', 'manuscript'])) {
            $docQuery = RecordDigitalDocument::query()->with('creator');

            if ($query) {
                $docQuery->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                });
            }

            if (!empty($validated['title'])) {
                $docQuery->where('name', 'like', "%{$validated['title']}%");
            }

            if (!empty($validated['author'])) {
                $docQuery->whereHas('creator', function($q) use ($validated) {
                    $q->where('name', 'like', "%{$validated['author']}%");
                });
            }

            if (!empty($validated['subject'])) {
                $docQuery->whereHas('keywords', function($q) use ($validated) {
                    $q->where('name', 'like', "%{$validated['subject']}%");
                });
            }

            if (!empty($validated['date_from'])) {
                $docQuery->where('document_date', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $docQuery->where('document_date', '<=', $validated['date_to']);
            }

            $documents = $docQuery->get()->map(function($doc) {
                return (object) [
                    'id' => $doc->id,
                    'type' => 'document',
                    'title' => $doc->name,
                    'description' => Str::limit($doc->description, 150),
                    'author' => $doc->creator ? $doc->creator->name : null,
                    'date' => $doc->document_date ? $doc->document_date->format('Y') : null,
                    'image' => null,
                    'url' => route('opac.digital.documents.show', $doc->id),
                    'model' => $doc
                ];
            });

            $results = $results->merge($documents);
        }

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
