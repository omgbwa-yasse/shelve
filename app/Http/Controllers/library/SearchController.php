<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordBook;
use App\Models\RecordAuthor;
use App\Models\RecordPeriodical;
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
            $results['books'] = RecordBook::with(['authors', 'publisher'])
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
        $publishers = RecordBook::with('publisher')
            ->get()
            ->pluck('publisher.name', 'publisher.id')
            ->unique()
            ->filter();

        $languages = RecordBook::select('language_id')
            ->distinct()
            ->whereNotNull('language_id')
            ->pluck('language_id');

        $categories = RecordBook::select('dewey')
            ->distinct()
            ->whereNotNull('dewey')
            ->orderBy('dewey')
            ->pluck('dewey');

        return view('library.search.advanced', compact('publishers', 'languages', 'categories'));
    }

    /**
     * Perform advanced search.
     */
    public function advancedSearch(Request $request)
    {
        $query = RecordBook::with(['authors', 'publisher']);

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
            $query->where('publisher_id', $request->publisher_id);
        }

        if ($request->filled('year_from')) {
            $query->where('publication_year', '>=', $request->year_from);
        }

        if ($request->filled('year_to')) {
            $query->where('publication_year', '<=', $request->year_to);
        }

        if ($request->filled('dewey')) {
            $query->where('dewey', 'like', $request->dewey . '%');
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
}
