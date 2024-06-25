<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\TermTranslation;
use Illuminate\Http\Request;

class TermTranslationController extends Controller
{
    public function index(Term $term)
    {
        $termTranslations = $term->load('translations');
        return view('thesaurus.term-translations.index', compact('term', 'termTranslations'));
    }

    public function create(Term $term)
    {
        return view('thesaurus.term-translations.create', compact('term'));
    }

    public function store(Request $request, Term $term)
    {
        $validatedData = $request->validate([
            'term2_id' => 'required|exists:terms,id',
        ]);

        $termTranslation = new TermTranslation([
            'term1_id' => $term->id,
            'term2_id' => $validatedData['term2_id'],
        ]);

        $termTranslation->save();

        return redirect()->route('term-translations.index', $term);
    }

    public function edit(Term $term, TermTranslation $termTranslation)
    {
        return view('thesaurus.term-translations.edit', compact('term', 'termTranslation'));
    }

    public function update(Request $request, Term $term, TermTranslation $termTranslation)
    {
        $validatedData = $request->validate([
            'term2_id' => 'required|exists:terms,id',
        ]);

        $termTranslation->update([
            'term2_id' => $validatedData['term2_id'],
        ]);

        return redirect()->route('term-translations.index', $term);
    }

    public function destroy(Term $term, TermTranslation $termTranslation)
    {
        dd($term);
        $termTranslation->delete();

        return redirect()->route('term-translations.index', $term);
    }
}
