<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\TermRelated;
use Illuminate\Http\Request;

class TermRelatedController extends Controller
{


    public function index(Term $term)
    {
        $relatedTerms = $term->relatedTerms()->with('relatedTerm')->get();

        return view('thesaurus.related.index', compact('term', 'relatedTerms'));
    }



    public function create(Term $term)
    {
        $terms = Term::where('id', '<>', $term->id)->get();

        return view('thesaurus.related.create', compact('term', 'terms'));
    }



    public function store(Request $request, Term $term)
    {
        $request->validate([
            'term_related_id' => 'required|exists:terms,id',
        ]);

        $term->relatedTerms()->create([
            'term_related_id' => $request->term_related_id,
        ]);

        return redirect()->route('term-related.index', $term)->with('success', 'Related term created successfully.');
    }



    public function show(Term $term, TermRelated $relatedTerm)
    {
        return view('thesaurus.related.show', compact('term', 'relatedTerm'));
    }


    public function edit(Term $term, TermRelated $relatedTerm)
    {
        $terms = Term::where('id', '<>', $term->id)->get();

        return view('thesaurus.related.edit', compact('term', 'relatedTerm', 'terms'));
    }


    public function update(Request $request, Term $term, TermRelated $relatedTerm)
    {
        $request->validate([
            'term_related_id' => 'required|exists:terms,id',
        ]);

        $relatedTerm->update([
            'term_related_id' => $request->term_related_id,
        ]);

        return redirect()->route('term-related.index', $term)->with('success', 'Related term updated successfully.');
    }


}
