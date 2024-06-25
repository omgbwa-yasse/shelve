<?php

namespace App\Http\Controllers;


use App\Models\Term;
use App\Models\TermEquivalent;
use App\Models\TermEquivalentType;
use Illuminate\Http\Request;

class TermEquivalentController extends Controller
{


    public function index(Term $term)
    {
        $termEquivalents = $term->termEquivalents()->with('child', 'equivalentType')->get();
        return view('thesaurus.term-equivalents.index', compact('term', 'termEquivalents'));
    }



    public function create(Term $term)
    {
        $terms = Term::where('id', '!=', $term->id)->get();
        $equivalentTypes = TermEquivalentType::all();
        return view('thesaurus.term-equivalents.create', compact('term', 'terms', 'equivalentTypes'));
    }


    public function store(Request $request, Term $term)
    {

        $request->validate([
            'term_id' => 'nullable|exists:terms,id',
            'term_used' => 'required|string|max:100',
            'equivalent_type_id' => 'required|exists:term_equivalent_types,id',
        ]);

        $term->equivalents()->create($request->all());

        return redirect()->route('term-equivalents.index', $term)
            ->with('success', 'Term equivalent created successfully.');
    }



    public function edit(Term $term, TermEquivalent $termEquivalent)
    {
        $terms = Term::where('id', '!=', $term->id)->get();
        $equivalentTypes = TermEquivalentType::all();
        return view('thesaurus.term-equivalents.edit', compact('term', 'termEquivalent', 'terms', 'equivalentTypes'));
    }



    public function update(Request $request, Term $term, TermEquivalent $termEquivalent)
    {
        $request->validate([
            'term_id' => 'nullable|exists:terms,id',
            'term_used' => 'required|string|max:100',
            'equivalent_type_id' => 'required|exists:term_equivalent_types,id',
        ]);

        $termEquivalent->update($request->all());

        return redirect()->route('term-equivalents.index', $term)
            ->with('success', 'Term equivalent updated successfully.');
    }



    public function destroy(Term $term, TermEquivalent $termEquivalent)
    {
        $termEquivalent->delete();

        return redirect()->route('term-equivalents.index', $term)
            ->with('success', 'Term equivalent deleted successfully.');
    }

    public function show(Term $term, TermEquivalent $termEquivalent)
    {
        return view('thesaurus.term-equivalents.show', compact('term', 'termEquivalent'));
    }

}
