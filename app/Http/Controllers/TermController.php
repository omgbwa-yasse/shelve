<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TermController extends Controller
{
    /**
     * Display a listing of the terms.
     */
    public function index()
    {
        $terms = Term::with(['broaderTerms', 'narrowerTerms', 'associatedTerms', 'nonDescriptors', 'externalAlignments'])
                     ->orderBy('preferred_label')
                     ->paginate(20);

        return view('thesaurus.terms.index', compact('terms'));
    }

    /**
     * Show the form for creating a new term.
     */
    public function create()
    {
        $languages = [
            'fr' => 'Français',
            'en' => 'Anglais',
            'es' => 'Espagnol',
            'de' => 'Allemand',
            'it' => 'Italien',
            'pt' => 'Portugais'
        ];

        $statuses = [
            'candidate' => 'Candidat',
            'approved' => 'Approuvé',
            'deprecated' => 'Obsolète'
        ];

        return view('thesaurus.terms.create', compact('languages', 'statuses'));
    }

    /**
     * Store a newly created term in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'preferred_label' => 'required|string|max:255',
            'language' => 'required|string|max:10',
            'status' => 'required|in:candidate,approved,deprecated',
            'category' => 'nullable|string|max:100',
            'notation' => 'nullable|string|max:50',
            'definition' => 'nullable|string|max:1000',
            'scope_note' => 'nullable|string|max:1000',
            'history_note' => 'nullable|string|max:1000',
            'editorial_note' => 'nullable|string|max:1000',
            'example' => 'nullable|string|max:1000',
            'is_top_term' => 'boolean'
        ]);

        $term = Term::create([
            'preferred_label' => $request->preferred_label,
            'language' => $request->language,
            'status' => $request->status,
            'category' => $request->category,
            'notation' => $request->notation,
            'definition' => $request->definition,
            'scope_note' => $request->scope_note,
            'history_note' => $request->history_note,
            'editorial_note' => $request->editorial_note,
            'example' => $request->example,
            'is_top_term' => $request->has('is_top_term')
        ]);

        return redirect()->route('terms.show', $term->id)
                         ->with('success', 'Terme créé avec succès.');
    }

    /**
     * Display the specified term.
     */
    public function show(Term $term)
    {
        $term->load([
            'broaderTerms',
            'narrowerTerms',
            'associatedTerms',
            'nonDescriptors',
            'externalAlignments',
            'translationsSource',
            'translationsTarget'
        ]);

        return view('thesaurus.terms.show', compact('term'));
    }

    /**
     * Show the form for editing the specified term.
     */
    public function edit(Term $term)
    {
        $languages = [
            'fr' => 'Français',
            'en' => 'Anglais',
            'es' => 'Espagnol',
            'de' => 'Allemand',
            'it' => 'Italien',
            'pt' => 'Portugais'
        ];

        $statuses = [
            'candidate' => 'Candidat',
            'approved' => 'Approuvé',
            'deprecated' => 'Obsolète'
        ];

        return view('thesaurus.terms.edit', compact('term', 'languages', 'statuses'));
    }

    /**
     * Update the specified term in storage.
     */
    public function update(Request $request, Term $term)
    {
        $request->validate([
            'preferred_label' => 'required|string|max:255',
            'language' => 'required|string|max:10',
            'status' => 'required|in:candidate,approved,deprecated',
            'category' => 'nullable|string|max:100',
            'notation' => 'nullable|string|max:50',
            'definition' => 'nullable|string|max:1000',
            'scope_note' => 'nullable|string|max:1000',
            'history_note' => 'nullable|string|max:1000',
            'editorial_note' => 'nullable|string|max:1000',
            'example' => 'nullable|string|max:1000',
            'is_top_term' => 'boolean'
        ]);

        $term->update([
            'preferred_label' => $request->preferred_label,
            'language' => $request->language,
            'status' => $request->status,
            'category' => $request->category,
            'notation' => $request->notation,
            'definition' => $request->definition,
            'scope_note' => $request->scope_note,
            'history_note' => $request->history_note,
            'editorial_note' => $request->editorial_note,
            'example' => $request->example,
            'is_top_term' => $request->has('is_top_term')
        ]);

        return redirect()->route('terms.show', $term->id)
                         ->with('success', 'Terme mis à jour avec succès.');
    }

    /**
     * Remove the specified term from storage.
     */
    public function destroy(Term $term)
    {
        // Vérifier s'il y a des relations
        if ($term->broaderTerms->count() > 0 ||
            $term->narrowerTerms->count() > 0 ||
            $term->associatedTerms->count() > 0 ||
            $term->nonDescriptors->count() > 0 ||
            $term->externalAlignments->count() > 0) {

            return redirect()->route('terms.index')
                             ->with('error', 'Impossible de supprimer ce terme car il a des relations avec d\'autres termes.');
        }

        $term->delete();

        return redirect()->route('terms.index')
                         ->with('success', 'Terme supprimé avec succès.');
    }

    /**
     * Browse terms in tree structure
     */
    public function browse()
    {
        // Récupérer les termes de tête pour commencer l'arbre
        $topTerms = Term::where('is_top_term', true)
                        ->with(['narrowerTerms' => function($query) {
                            $query->orderBy('preferred_label');
                        }])
                        ->orderBy('preferred_label')
                        ->get();

        return view('thesaurus.terms.browse', compact('topTerms'));
    }

    /**
     * Get children terms for AJAX tree navigation
     */
    public function getChildren(Request $request, Term $term)
    {
        $children = $term->narrowerTerms()
                         ->with(['narrowerTerms' => function($query) {
                             $query->orderBy('preferred_label');
                         }])
                         ->orderBy('preferred_label')
                         ->get();

        return response()->json([
            'success' => true,
            'children' => $children->map(function ($child) {
                return [
                    'id' => $child->id,
                    'preferred_label' => $child->preferred_label,
                    'status' => $child->status,
                    'has_children' => $child->narrowerTerms->count() > 0,
                    'url' => route('terms.show', $child->id)
                ];
            })
        ]);
    }

    /**
     * Search terms for autocomplete
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $terms = Term::where('preferred_label', 'LIKE', "%{$query}%")
                     ->orWhereHas('nonDescriptors', function($q) use ($query) {
                         $q->where('non_descriptor_label', 'LIKE', "%{$query}%");
                     })
                     ->orderBy('preferred_label')
                     ->limit($limit)
                     ->get();

        return response()->json($terms->map(function($term) {
            return [
                'id' => $term->id,
                'text' => $term->preferred_label,
                'preferred_label' => $term->preferred_label,
                'language' => $term->language,
                'status' => $term->status,
                'category' => $term->category,
                'url' => route('terms.show', $term->id)
            ];
        }));
    }
}
