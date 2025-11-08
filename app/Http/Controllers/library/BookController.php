<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordBook;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index(Request $request)
    {
        $query = RecordBook::with(['publisher', 'language', 'format', 'binding', 'authors']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%");
            });
        }

        // Category/Dewey filter
        if ($request->filled('category')) {
            $query->where('dewey', 'like', $request->category . '%');
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
        return view('library.books.create');
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
            'publisher_id' => 'nullable|exists:record_book_publishers,id',
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 5),
            'edition' => 'nullable|string|max:100',
            'dewey' => 'nullable|string|max:50',
            'pages' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $validated['creator_id'] = auth()->id();
        $validated['organisation_id'] = auth()->user()->current_organisation_id;

        $book = RecordBook::create($validated);

        return redirect()->route('library.books.show', $book->id)
            ->with('success', 'Livre créé avec succès.');
    }

    /**
     * Display the specified book.
     */
    public function show($id)
    {
        $book = RecordBook::with(['publisher', 'authors', 'copies', 'loans', 'reservations'])
            ->findOrFail($id);

        return view('library.books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit($id)
    {
        $book = RecordBook::findOrFail($id);
        return view('library.books.edit', compact('book'));
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
            'publisher_id' => 'nullable|exists:record_book_publishers,id',
            'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 5),
            'edition' => 'nullable|string|max:100',
            'dewey' => 'nullable|string|max:50',
            'pages' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $book->update($validated);

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
}
