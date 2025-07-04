<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermController extends Controller
{
    public function index()
    {
        $terms = Term::orderBy('preferred_label')->get();
        return view('thesaurus.terms.index', compact('terms'));
    }

    public function create()
    {
        $languages = ['fr' => 'Français', 'en' => 'Anglais', 'es' => 'Espagnol',
                      'de' => 'Allemand', 'it' => 'Italien', 'pt' => 'Portugais'];
        $statuses = ['approved' => 'Approuvé', 'candidate' => 'Candidat', 'deprecated' => 'Obsolète'];

        return view('thesaurus.terms.create', compact('languages', 'statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'preferred_label' => 'required|string|max:100',
            'definition' => 'nullable|string',
            'scope_note' => 'nullable|string',
            'history_note' => 'nullable|string',
            'example' => 'nullable|string',
            'editorial_note' => 'nullable|string',
            'language' => 'required|string|in:fr,en,es,de,it,pt',
            'category' => 'nullable|string|max:100',
            'status' => 'required|string|in:approved,candidate,deprecated',
            'notation' => 'nullable|string|max:50',
            'is_top_term' => 'boolean',
        ]);

        $termData = $request->all();
        $termData['is_top_term'] = $request->has('is_top_term');

        Term::create($termData);

        return redirect()->route('terms.index')
            ->with('success', 'Terme créé avec succès.');
    }

    public function show(Term $term)
    {
        $term->load('broaderTerms', 'narrowerTerms', 'nonDescriptors', 'externalAlignments');

        return view('thesaurus.terms.show', compact('term'));
    }

    public function edit(Term $term)
    {
        $languages = ['fr' => 'Français', 'en' => 'Anglais', 'es' => 'Espagnol',
                      'de' => 'Allemand', 'it' => 'Italien', 'pt' => 'Portugais'];
        $statuses = ['approved' => 'Approuvé', 'candidate' => 'Candidat', 'deprecated' => 'Obsolète'];

        return view('thesaurus.terms.edit', compact('term', 'languages', 'statuses'));
    }

    public function update(Request $request, Term $term)
    {
        $request->validate([
            'preferred_label' => 'required|string|max:100',
            'definition' => 'nullable|string',
            'scope_note' => 'nullable|string',
            'history_note' => 'nullable|string',
            'example' => 'nullable|string',
            'editorial_note' => 'nullable|string',
            'language' => 'required|string|in:fr,en,es,de,it,pt',
            'category' => 'nullable|string|max:100',
            'status' => 'required|string|in:approved,candidate,deprecated',
            'notation' => 'nullable|string|max:50',
            'is_top_term' => 'boolean',
        ]);

        $termData = $request->all();
        $termData['is_top_term'] = $request->has('is_top_term');

        $term->update($termData);

        return redirect()->route('terms.index')
            ->with('success', 'Terme mis à jour avec succès');
    }

    public function destroy(Term $term)
    {
        $term->delete();

        return redirect()->route('terms.index')
            ->with('success', 'Terme supprimé avec succès');
    }
}


