<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordAuthor;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    /**
     * Display a listing of authors.
     */
    public function index(Request $request)
    {
        $query = RecordAuthor::withCount('books');

        // Recherche par nom, pseudonyme ou biographie
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtrer par nationalité
        if ($request->filled('nationality')) {
            $query->byNationality($request->nationality);
        }

        // Filtrer par statut (vivant/décédé)
        if ($request->filled('status')) {
            if ($request->status === 'alive') {
                $query->alive();
            } elseif ($request->status === 'deceased') {
                $query->deceased();
            }
        }

        // Tri
        $sortBy = $request->get('sort_by', 'full_name');
        $sortOrder = $request->get('sort_order', 'asc');

        if ($sortBy === 'books_count') {
            $query->orderBy('books_count', $sortOrder);
        } elseif ($sortBy === 'birth_year') {
            $query->orderBy('birth_year', $sortOrder);
        } else {
            $query->orderBy('full_name', $sortOrder);
        }

        $authors = $query->paginate(20);

        // Statistiques
        $stats = [
            'total_authors' => RecordAuthor::count(),
            'alive' => RecordAuthor::alive()->count(),
            'deceased' => RecordAuthor::deceased()->count(),
            'nationalities' => RecordAuthor::distinct('nationality')->whereNotNull('nationality')->count(),
        ];

        return view('library.authors.index', compact('authors', 'stats'));
    }

    /**
     * Show the form for creating a new author.
     */
    public function create()
    {
        return view('library.authors.create');
    }

    /**
     * Store a newly created author in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'full_name' => 'required|string|max:500',
            'pseudonym' => 'nullable|string|max:255',
            'birth_year' => 'nullable|integer|min:1|max:' . date('Y'),
            'death_year' => 'nullable|integer|min:1|max:' . date('Y'),
            'birth_place' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'biography' => 'nullable|string',
            'specializations' => 'nullable|array',
            'website' => 'nullable|url|max:255',
            'orcid' => 'nullable|string|max:50',
            'isni' => 'nullable|string|max:50',
            'viaf' => 'nullable|string|max:50',
            'status' => 'required|in:active,deceased,unknown',
        ]);

        $author = RecordAuthor::create($validated);

        return redirect()->route('library.authors.index')
            ->with('success', 'Auteur créé avec succès.');
    }

    /**
     * Display the specified author.
     */
    public function show($id)
    {
        $author = RecordAuthor::with(['books' => function ($query) {
            $query->withPivot('role')->orderBy('publication_year', 'desc');
        }])->findOrFail($id);

        // Statistiques de l'auteur
        $stats = [
            'total_books' => $author->books()->count(),
            'as_author' => $author->authoredBooks()->count(),
            'as_editor' => $author->editedBooks()->count(),
            'as_translator' => $author->translatedBooks()->count(),
            'books_by_year' => $author->books()
                ->selectRaw('publication_year, COUNT(*) as count')
                ->whereNotNull('publication_year')
                ->groupBy('publication_year')
                ->orderBy('publication_year')
                ->get(),
        ];

        return view('library.authors.show', compact('author', 'stats'));
    }

    /**
     * Show the form for editing the specified author.
     */
    public function edit($id)
    {
        $author = RecordAuthor::findOrFail($id);
        return view('library.authors.edit', compact('author'));
    }

    /**
     * Update the specified author in storage.
     */
    public function update(Request $request, $id)
    {
        $author = RecordAuthor::findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'full_name' => 'required|string|max:500',
            'pseudonym' => 'nullable|string|max:255',
            'birth_year' => 'nullable|integer|min:1|max:' . date('Y'),
            'death_year' => 'nullable|integer|min:1|max:' . date('Y'),
            'birth_place' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'biography' => 'nullable|string',
            'specializations' => 'nullable|array',
            'website' => 'nullable|url|max:255',
            'orcid' => 'nullable|string|max:50',
            'isni' => 'nullable|string|max:50',
            'viaf' => 'nullable|string|max:50',
            'status' => 'required|in:active,deceased,unknown',
        ]);

        $author->update($validated);

        // Mettre à jour le compteur de livres
        $author->updateBookCount();

        return redirect()->route('library.authors.index')
            ->with('success', 'Auteur modifié avec succès.');
    }

    /**
     * Remove the specified author from storage.
     */
    public function destroy($id)
    {
        $author = RecordAuthor::findOrFail($id);

        // Vérifier s'il a des livres associés
        if ($author->books()->count() > 0) {
            return redirect()->route('library.authors.index')
                ->with('error', 'Impossible de supprimer un auteur ayant des livres associés.');
        }

        $author->delete();

        return redirect()->route('library.authors.index')
            ->with('success', 'Auteur supprimé avec succès.');
    }
}
