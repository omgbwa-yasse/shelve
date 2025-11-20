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
