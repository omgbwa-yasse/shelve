<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\TermEquivalent;
use Illuminate\Http\Request;

class TermEquivalentController extends Controller
{
    public function index(Term $term)
    {
        $termEquivalents = $term->load('equivalents');
        return view('thesaurus.term-equivalents.index', compact('term', 'termEquivalents'));
    }

    public function create(Term $term)
    {
        return view('thesaurus.term-equivalents.create', compact('term'));
    }

    public function store(Request $request, Term $term)
    {
        $validatedData = $request->validate([
            'term2_id' => 'required|exists:terms,id',
        ]);

        $termEquivalent = new TermEquivalent([
            'term1_id' => $term->id,
            'term2_id' => $validatedData['term2_id'],
        ]);

        $termEquivalent->save();

        return redirect()->route('term-equivalents.index', $term);
    }

    public function edit(Term $term, TermEquivalent $termEquivalent)
    {
        return view('thesaurus.term-equivalents.edit', compact('term', 'termEquivalent'));
    }

    public function update(Request $request, Term $term, TermEquivalent $termEquivalent)
    {
        $validatedData = $request->validate([
            'term2_id' => 'required|exists:terms,id',
        ]);

        $termEquivalent->update([
            'term2_id' => $validatedData['term2_id'],
        ]);

        return redirect()->route('term-equivalents.index', $term);
    }

    public function destroy(Term $term, TermEquivalent $termEquivalent)
    {
        dd($term);
        $termEquivalent->delete();

        return redirect()->route('term-equivalents.index', $term);
    }
}
