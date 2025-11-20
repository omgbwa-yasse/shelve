<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordBook;
use App\Models\RecordAuthor;
use App\Models\RecordPeriodical;
use App\Models\BookClassification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    /**
     * Display search interface.
     */
    public function index()
    {
        return view('library.search.index');
    }

    /**
     * Perform search.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');
        $type = $request->input('type', 'all'); // all, books, authors, periodicals

        $results = [];

        if ($type === 'all' || $type === 'books') {
            $results['books'] = RecordBook::with(['authors', 'publishers'])
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('subtitle', 'like', "%{$query}%")
                        ->orWhere('isbn', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->limit(20)
                ->get();
        }

        if ($type === 'all' || $type === 'authors') {
            $results['authors'] = RecordAuthor::search($query)
                ->withCount('books')
                ->limit(20)
                ->get();
        }

        if ($type === 'all' || $type === 'periodicals') {
            $results['periodicals'] = RecordPeriodical::where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('issn', 'like', "%{$query}%")
                        ->orWhere('publisher', 'like', "%{$query}%");
                })
                ->limit(20)
                ->get();
        }

        // Enregistrer dans l'historique de recherche
        $this->saveRecentSearch($query);

        return view('library.search.results', compact('results', 'query', 'type'));
    }

    /**
     * Display advanced search.
     */
    public function advanced()
    {
        // Récupérer les options pour les filtres
        $publishers = \App\Models\RecordBookPublisher::orderBy('name')
            ->pluck('name', 'id');

        $languages = \App\Models\RecordLanguage::orderBy('name')
            ->pluck('name', 'id');

        $classifications = BookClassification::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('library.search.advanced', compact('publishers', 'languages', 'classifications'));
    }

    /**
     * Perform advanced search.
     */
    public function advancedSearch(Request $request)
    {
        $query = RecordBook::with(['authors', 'publishers']);

        if ($request->filled('title')) {
            $query->where('title', 'like', "%{$request->title}%");
        }

        if ($request->filled('author')) {
            $query->whereHas('authors', function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->author}%");
            });
        }

        if ($request->filled('isbn')) {
            $query->where('isbn', 'like', "%{$request->isbn}%");
        }

        if ($request->filled('publisher_id')) {
            $query->whereHas('publishers', function ($q) use ($request) {
                $q->where('record_book_publishers.id', $request->publisher_id);
            });
        }

        if ($request->filled('year_from')) {
            $query->where('publication_year', '>=', $request->year_from);
        }

        if ($request->filled('year_to')) {
            $query->where('publication_year', '<=', $request->year_to);
        }

        if ($request->filled('classification_id')) {
            $query->whereHas('classifications', function ($q) use ($request) {
                $q->where('id', $request->classification_id);
            });
        }

        if ($request->filled('language_id')) {
            $query->where('language_id', $request->language_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'available') {
                $query->where('available_copies', '>', 0);
            } elseif ($request->status === 'unavailable') {
                $query->where('available_copies', 0);
            }
        }

        $results = ['books' => $query->paginate(20)];
        $type = 'books';

        return view('library.search.results', compact('results', 'type'));
    }

    /**
     * Display popular searches.
     */
    public function popular()
    {
        // Récupérer les recherches populaires depuis le cache
        $popularSearches = Cache::get('popular_searches', []);

        // Trier par nombre d'occurrences
        arsort($popularSearches);

        $popularSearches = array_slice($popularSearches, 0, 20, true);

        return view('library.search.popular', compact('popularSearches'));
    }

    /**
     * Display recent searches.
     */
    public function recent()
    {
        // Récupérer les recherches récentes pour l'utilisateur
        $recentSearches = session('recent_searches', []);

        return view('library.search.recent', compact('recentSearches'));
    }

    /**
     * Save a search to recent history.
     */
    protected function saveRecentSearch($query)
    {
        // Sauvegarder dans la session utilisateur
        $recentSearches = session('recent_searches', []);

        // Ajouter en début de liste
        array_unshift($recentSearches, [
            'query' => $query,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Garder seulement les 10 dernières
        $recentSearches = array_slice($recentSearches, 0, 10);

        session(['recent_searches' => $recentSearches]);

        // Mettre à jour les recherches populaires dans le cache
        $popularSearches = Cache::get('popular_searches', []);
        $popularSearches[$query] = ($popularSearches[$query] ?? 0) + 1;
        Cache::put('popular_searches', $popularSearches, now()->addDays(30));
    }

    /**
     * Autocomplete for various entities.
     */
    public function autocomplete(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3',
            'type' => 'required|string|in:publishers,languages,formats,bindings,series,authors,books',
        ]);

        $query = $request->input('q');
        $type = $request->input('type');
        $limit = 10;

        $results = [];

        switch ($type) {
            case 'publishers':
                $results = \App\Models\RecordBookPublisher::where('name', 'like', "%{$query}%")
                    ->select('id', 'name as text')
                    ->limit($limit)
                    ->get();
                break;
            case 'languages':
                $results = \App\Models\RecordLanguage::where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->select('id', 'name as text')
                    ->limit($limit)
                    ->get();
                break;
            case 'formats':
                $results = \App\Models\RecordBookFormat::where('name', 'like', "%{$query}%")
                    ->select('id', 'name as text')
                    ->limit($limit)
                    ->get();
                break;
            case 'bindings':
                $results = \App\Models\RecordBookBinding::where('name', 'like', "%{$query}%")
                    ->select('id', 'name as text')
                    ->limit($limit)
                    ->get();
                break;
            case 'series':
                $results = \App\Models\RecordBookPublisherSeries::where('name', 'like', "%{$query}%")
                    ->select('id', 'name as text')
                    ->limit($limit)
                    ->get();
                break;
            case 'authors':
                $results = \App\Models\RecordAuthor::where('full_name', 'like', "%{$query}%")
                    ->select('id', 'full_name as text')
                    ->limit($limit)
                    ->get();
                break;
            case 'books':
                $results = \App\Models\RecordBook::where('title', 'like', "%{$query}%")
                    ->orWhere('isbn', 'like', "%{$query}%")
                    ->select('id', 'title as text')
                    ->limit($limit)
                    ->get();
                break;
        }

        return response()->json([
            'results' => $results
        ]);
    }
}
