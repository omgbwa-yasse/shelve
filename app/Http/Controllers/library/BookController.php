<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordBook;
use App\Models\BookClassification;
use App\Models\Term;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index(Request $request)
    {
        $query = RecordBook::with(['publishers', 'language', 'format', 'binding', 'authors', 'classifications']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%");
            });
        }

        // Classification filter
        if ($request->filled('classification_id')) {
            $query->whereHas('classifications', function($q) use ($request) {
                $q->where('id', $request->classification_id);
            });
        }

        // Term filter
        if ($request->filled('term_id')) {
            $query->whereHas('terms', function($q) use ($request) {
                $q->where('id', $request->term_id);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $books = $query->orderBy('title')->paginate(20);

        return view('library.books.index', compact('books'));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create()
    {
        $publishers = \App\Models\RecordBookPublisher::orderBy('name')->get();
        $languages = \App\Models\RecordLanguage::orderBy('name')->get();
        $formats = \App\Models\RecordBookFormat::orderBy('name')->get();
        $bindings = \App\Models\RecordBookBinding::orderBy('name')->get();
        $series = \App\Models\RecordBookPublisherSeries::orderBy('name')->get();

        return view('library.books.create', compact('publishers', 'languages', 'formats', 'bindings', 'series'));
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'isbn' => 'nullable|string|max:20',
            'title' => 'required|string|max:500',
            'subtitle' => 'nullable|string|max:500',
            'publishers' => 'nullable|array',
            'publishers.*' => 'exists:record_book_publishers,id',
            'series_id' => 'nullable|exists:record_book_publisher_series,id',
            'language_id' => 'nullable|exists:record_languages,id',
            'format_id' => 'nullable|exists:record_book_formats,id',
            'binding_id' => 'nullable|exists:record_book_bindings,id',
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 5),
            'edition' => 'nullable|string|max:100',
            'pages' => 'nullable|integer',
            'dimensions' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',

            // New relations
            'authors' => 'nullable|array',
            'authors.*.id' => 'exists:record_authors,id',
            'authors.*.responsibility_type' => 'nullable|string',
            'authors.*.function' => 'nullable|string',

            'classifications' => 'nullable|array',
            'classifications.*' => 'exists:classifications,id',

            'terms' => 'nullable|array',
            'terms.*' => 'exists:terms,id',

            'collections' => 'nullable|array',
            'collections.*.id' => 'exists:record_book_publisher_series,id',
            'collections.*.collection_number' => 'nullable|string',

            'publisher_places' => 'nullable|array',
            'publisher_places.*.place' => 'required|string',
        ]);

        $validated['creator_id'] = auth()->id();
        $validated['organisation_id'] = auth()->user()->current_organisation_id;

        $book = RecordBook::create($validated);

        if (isset($validated['publishers'])) {
            $book->publishers()->sync($validated['publishers']);
        }

        // Sync Authors
        if (isset($validated['authors'])) {
            $syncData = [];
            foreach ($validated['authors'] as $index => $author) {
                $syncData[$author['id']] = [
                    'responsibility_type' => $author['responsibility_type'] ?? null,
                    'function' => $author['function'] ?? null,
                    'display_order' => $index + 1
                ];
            }
            $book->authors()->sync($syncData);
        }

        // Sync Classifications
        if (isset($validated['classifications'])) {
            $syncData = [];
            foreach ($validated['classifications'] as $index => $id) {
                 $syncData[$id] = ['display_order' => $index + 1];
            }
            $book->classifications()->sync($syncData);
        }

        // Sync Terms
        if (isset($validated['terms'])) {
            $syncData = [];
            foreach ($validated['terms'] as $index => $id) {
                 $syncData[$id] = ['display_order' => $index + 1];
            }
            $book->terms()->sync($syncData);
        }

        // Sync Collections
        if (isset($validated['collections'])) {
            $syncData = [];
            foreach ($validated['collections'] as $collection) {
                $syncData[$collection['id']] = ['collection_number' => $collection['collection_number'] ?? null];
            }
            $book->collections()->sync($syncData);
        }

        // Create Publisher Places
        if (isset($validated['publisher_places'])) {
            foreach ($validated['publisher_places'] as $index => $place) {
                $book->publisherPlaces()->create([
                    'publication_place' => $place['place'],
                    'display_order' => $index + 1
                ]);
            }
        }

        return redirect()->route('library.books.show', $book->id)
            ->with('success', 'Livre créé avec succès.');
    }

    /**
     * Display the specified book.
     */
    public function show($id)
    {
        $book = RecordBook::with(['publishers', 'authors', 'copies', 'loans', 'reservations'])
            ->findOrFail($id);

        return view('library.books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit($id)
    {
        $book = RecordBook::findOrFail($id);
        $publishers = \App\Models\RecordBookPublisher::orderBy('name')->get();
        $languages = \App\Models\RecordLanguage::orderBy('name')->get();
        $formats = \App\Models\RecordBookFormat::orderBy('name')->get();
        $bindings = \App\Models\RecordBookBinding::orderBy('name')->get();
        $series = \App\Models\RecordBookPublisherSeries::orderBy('name')->get();

        return view('library.books.edit', compact('book', 'publishers', 'languages', 'formats', 'bindings', 'series'));
    }

    /**
     * Update the specified book in storage.
     */
    public function update(Request $request, $id)
    {
        $book = RecordBook::findOrFail($id);

        $validated = $request->validate([
            'isbn' => 'nullable|string|max:20',
            'title' => 'required|string|max:500',
            'subtitle' => 'nullable|string|max:500',
            'publishers' => 'nullable|array',
            'publishers.*' => 'exists:record_book_publishers,id',
            'series_id' => 'nullable|exists:record_book_publisher_series,id',
            'language_id' => 'nullable|exists:record_languages,id',
            'format_id' => 'nullable|exists:record_book_formats,id',
            'binding_id' => 'nullable|exists:record_book_bindings,id',
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 5),
            'edition' => 'nullable|string|max:100',
            'pages' => 'nullable|integer',
            'dimensions' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',

            // New relations
            'authors' => 'nullable|array',
            'authors.*.id' => 'exists:record_authors,id',
            'authors.*.responsibility_type' => 'nullable|string',
            'authors.*.function' => 'nullable|string',

            'classifications' => 'nullable|array',
            'classifications.*' => 'exists:classifications,id',

            'terms' => 'nullable|array',
            'terms.*' => 'exists:terms,id',

            'collections' => 'nullable|array',
            'collections.*.id' => 'exists:record_book_publisher_series,id',
            'collections.*.collection_number' => 'nullable|string',

            'publisher_places' => 'nullable|array',
            'publisher_places.*.place' => 'required|string',
        ]);

        $book->update($validated);

        if (isset($validated['publishers'])) {
            $book->publishers()->sync($validated['publishers']);
        } else {
            $book->publishers()->detach();
        }

        // Sync Authors
        if (isset($validated['authors'])) {
            $syncData = [];
            foreach ($validated['authors'] as $index => $author) {
                $syncData[$author['id']] = [
                    'responsibility_type' => $author['responsibility_type'] ?? null,
                    'function' => $author['function'] ?? null,
                    'display_order' => $index + 1
                ];
            }
            $book->authors()->sync($syncData);
        } else {
            $book->authors()->detach();
        }

        // Sync Classifications
        if (isset($validated['classifications'])) {
            $syncData = [];
            foreach ($validated['classifications'] as $index => $id) {
                 $syncData[$id] = ['display_order' => $index + 1];
            }
            $book->classifications()->sync($syncData);
        } else {
            $book->classifications()->detach();
        }

        // Sync Terms
        if (isset($validated['terms'])) {
            $syncData = [];
            foreach ($validated['terms'] as $index => $id) {
                 $syncData[$id] = ['display_order' => $index + 1];
            }
            $book->terms()->sync($syncData);
        } else {
            $book->terms()->detach();
        }

        // Sync Collections
        if (isset($validated['collections'])) {
            $syncData = [];
            foreach ($validated['collections'] as $collection) {
                $syncData[$collection['id']] = ['collection_number' => $collection['collection_number'] ?? null];
            }
            $book->collections()->sync($syncData);
        } else {
            $book->collections()->detach();
        }

        // Update Publisher Places (Delete all and recreate for simplicity, or update smart)
        // For simplicity, we'll delete and recreate as it's a simple list
        $book->publisherPlaces()->delete();
        if (isset($validated['publisher_places'])) {
            foreach ($validated['publisher_places'] as $index => $place) {
                $book->publisherPlaces()->create([
                    'publication_place' => $place['place'],
                    'display_order' => $index + 1
                ]);
            }
        }

        return redirect()->route('library.books.show', $book->id)
            ->with('success', 'Livre modifié avec succès.');
    }

    /**
     * Remove the specified book from storage.
     */
    public function destroy($id)
    {
        $book = RecordBook::findOrFail($id);
        $book->delete();

        return redirect()->route('library.books.index')
            ->with('success', 'Livre supprimé avec succès.');
    }

    /**
     * Duplicate a book.
     */
    public function duplicate($id)
    {
        // TODO: Implémenter la logique de duplication
        return redirect()->route('library.books.index')
            ->with('success', 'Livre dupliqué avec succès.');
    }

    /**
     * Show import form.
     */
    public function import(Request $request)
    {
        // TODO: Implémenter l'import de livres
        return redirect()->route('library.books.index')
            ->with('success', 'Import réussi.');
    }

    /**
     * Show export form.
     */
    public function exportForm()
    {
        // TODO: Afficher le formulaire d'export
        return view('library.books.export');
    }

    /**
     * Export books.
     */
    public function export(Request $request)
    {
        // TODO: Implémenter l'export de livres
        return redirect()->route('library.books.index')
            ->with('success', 'Export réussi.');
    }

    /**
     * Search publishers via AJAX for autocomplete.
     */
    public function searchPublishers(Request $request)
    {
        $search = $request->input('q', '');
        $page = $request->input('page', 1);
        $perPage = 20;

        $query = \App\Models\RecordBookPublisher::query()
            ->where('status', 'active')
            ->orderBy('name');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $publishers = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name', 'country', 'city']);

        $results = $publishers->map(function($publisher) {
            $text = $publisher->name;
            if ($publisher->city && $publisher->country) {
                $text .= " ({$publisher->city}, {$publisher->country})";
            }
            return [
                'id' => $publisher->id,
                'text' => $text,
                'name' => $publisher->name,
                'country' => $publisher->country,
                'city' => $publisher->city,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Search series via AJAX for autocomplete.
     */
    public function searchSeries(Request $request)
    {
        $search = $request->input('q', '');
        $publisherId = $request->input('publisher_id');
        $page = $request->input('page', 1);
        $perPage = 20;

        $query = \App\Models\RecordBookPublisherSeries::query()
            ->with('publisher')
            ->where('status', 'active')
            ->orderBy('name');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($publisherId) {
            $query->where('publisher_id', $publisherId);
        }

        $total = $query->count();
        $series = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name', 'publisher_id', 'issn']);

        $results = $series->map(function($s) {
            $text = $s->name;
            if ($s->publisher) {
                $text .= " - {$s->publisher->name}";
            }
            return [
                'id' => $s->id,
                'text' => $text,
                'name' => $s->name,
                'publisher_id' => $s->publisher_id,
                'publisher_name' => $s->publisher?->name,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Search authors via AJAX for autocomplete.
     */
    public function searchAuthors(Request $request)
    {
        $search = $request->input('q', '');
        $page = $request->input('page', 1);
        $perPage = 20;

        $query = \App\Models\RecordAuthor::query()
            ->where('status', 'active')
            ->orderBy('full_name');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('pseudonym', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $authors = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'full_name', 'pseudonym', 'birth_year', 'death_year']);

        $results = $authors->map(function($author) {
            $text = $author->full_name;
            if ($author->pseudonym) {
                $text .= " ({$author->pseudonym})";
            }
            if ($author->birth_year) {
                $years = $author->birth_year;
                if ($author->death_year) {
                    $years .= "-{$author->death_year}";
                }
                $text .= " [{$years}]";
            }
            return [
                'id' => $author->id,
                'text' => $text,
                'full_name' => $author->full_name,
                'pseudonym' => $author->pseudonym,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Search classifications via AJAX for autocomplete.
     */
    public function searchClassifications(Request $request)
    {
        $search = $request->input('q', '');
        $page = $request->input('page', 1);
        $perPage = 20;

        $query = BookClassification::query()
            ->orderBy('name');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $classifications = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'name', 'description']);

        $results = $classifications->map(function($class) {
            $text = $class->name;
            if ($class->description) {
                $text .= " - " . \Illuminate\Support\Str::limit($class->description, 50);
            }
            return [
                'id' => $class->id,
                'text' => $text,
                'name' => $class->name,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Store a new publisher via AJAX.
     */
    public function storePublisher(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
        ]);

        $publisher = \App\Models\RecordBookPublisher::create(array_merge($validated, [
            'status' => 'active'
        ]));

        return response()->json([
            'id' => $publisher->id,
            'text' => $publisher->name,
            'name' => $publisher->name,
        ]);
    }

    /**
     * Store a new series via AJAX.
     */
    public function storeSeries(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'publisher_id' => 'nullable|exists:record_book_publishers,id',
        ]);

        $series = \App\Models\RecordBookPublisherSeries::create(array_merge($validated, [
            'status' => 'active'
        ]));

        $text = $series->name;
        if ($series->publisher) {
            $text .= " - {$series->publisher->name}";
        }

        return response()->json([
            'id' => $series->id,
            'text' => $text,
            'name' => $series->name,
        ]);
    }

    /**
     * Store a new author via AJAX.
     */
    public function storeAuthor(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'pseudonym' => 'nullable|string|max:100',
        ]);

        $author = \App\Models\RecordAuthor::create(array_merge($validated, [
            'status' => 'active'
        ]));

        return response()->json([
            'id' => $author->id,
            'text' => $author->full_name,
            'full_name' => $author->full_name,
        ]);
    }

    /**
     * Store a new classification via AJAX.
     */
    public function storeClassification(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $classification = BookClassification::create($validated);

        return response()->json([
            'id' => $classification->id,
            'text' => $classification->name,
            'name' => $classification->name,
        ]);
    }

    /**
     * Search thesaurus terms via AJAX for autocomplete.
     */
    public function searchThesaurus(Request $request)
    {
        $search = $request->input('q', '');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 20);
        $perPage = min($limit, 20); // Maximum 20

        $query = \App\Models\ThesaurusConcept::query()
            ->with(['labels' => function($q) {
                $q->where('language', 'fr')
                  ->where('type', 'prefLabel');
            }])
            ->where('status', 'active')
            ->orderBy('notation');

        if ($search) {
            $query->whereHas('labels', function($q) use ($search) {
                $q->where('value', 'like', "%{$search}%")
                  ->where('language', 'fr');
            });
        }

        $total = $query->count();
        $terms = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $results = $terms->map(function($term) {
            $prefLabel = $term->labels->first();
            $text = $prefLabel ? $prefLabel->value : $term->notation;

            if ($term->notation) {
                $text .= " [{$term->notation}]";
            }

            return [
                'id' => $term->id,
                'text' => $text,
                'name' => $prefLabel ? $prefLabel->value : '',
                'notation' => $term->notation,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Store a new thesaurus term via AJAX.
     */
    public function storeThesaurus(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'notation' => 'nullable|string|max:50',
            'scheme_id' => 'nullable|exists:thesaurus_schemes,id',
        ]);

        // Créer le concept
        $concept = \App\Models\ThesaurusConcept::create([
            'scheme_id' => $validated['scheme_id'] ?? null,
            'notation' => $validated['notation'] ?? null,
            'status' => 'active',
        ]);

        // Créer le label préféré
        $label = \App\Models\ThesaurusLabel::create([
            'concept_id' => $concept->id,
            'value' => $validated['name'],
            'language' => 'fr',
            'type' => 'prefLabel',
        ]);

        $text = $validated['name'];
        if ($validated['notation'] ?? null) {
            $text .= " [{$validated['notation']}]";
        }

        return response()->json([
            'id' => $concept->id,
            'text' => $text,
            'name' => $validated['name'],
        ]);
    }

    /**
     * Get data for the selection modal.
     */
    public function getModalData(Request $request)
    {
        $type = $request->input('type');
        $items = collect();

        if ($type === 'publishers') {
            $items = \App\Models\RecordBookPublisher::orderBy('name')->get();
        } elseif ($type === 'series') {
            $items = \App\Models\RecordBookPublisherSeries::with('publisher')->orderBy('name')->get();
        }

        // Group by first letter
        $grouped = $items->groupBy(function ($item) {
            $name = $item->name ?? $item->title;
            $first = strtoupper(substr($name, 0, 1));
            if (is_numeric($first)) return '1';
            return ctype_alpha($first) ? $first : '#';
        });

        // Ensure order: A-Z, then #, then 1
        $sorted = [];
        foreach (range('A', 'Z') as $char) {
            if (isset($grouped[$char])) $sorted[$char] = $grouped[$char];
        }
        if (isset($grouped['#'])) $sorted['#'] = $grouped['#'];
        if (isset($grouped['1'])) $sorted['1'] = $grouped['1'];

        return response()->json($sorted);
    }

    /**
     * Store a new item from the modal.
     */
    public function storeModalData(Request $request)
    {
        $type = $request->input('type');
        $name = $request->input('name');

        if (!$name) {
            return response()->json(['error' => 'Le nom est requis'], 422);
        }

        $item = null;

        try {
            if ($type === 'publishers') {
                $item = \App\Models\RecordBookPublisher::create(['name' => $name, 'status' => 'active']);
            } elseif ($type === 'series') {
                $publisherId = $request->input('publisher_id');
                $data = ['name' => $name, 'status' => 'active'];
                if ($publisherId) {
                    $data['publisher_id'] = $publisherId;
                }
                $item = \App\Models\RecordBookPublisherSeries::create($data);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la création: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'id' => $item->id,
            'text' => $item->name
        ]);
    }
}
