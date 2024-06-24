<?php

namespace App\Http\Controllers;


use App\Models\Term;
use App\Models\TermRelation;
use App\Models\TermRelationType;
use Illuminate\Http\Request;

class TermRelationController extends Controller
{


    public function index(Term $term)
    {
        $termRelations = $term->termRelations()->with('child', 'relationType')->get();
        return view('thesaurus.term-relations.index', compact('term', 'termRelations'));
    }



    public function create(Term $term)
    {
        $terms = Term::where('id', '!=', $term->id)->get();
        $relationTypes = TermRelationType::all();
        return view('thesaurus.term-relations.create', compact('term', 'terms', 'relationTypes'));
    }


    public function store(Request $request, Term $term)
    {

        $request->validate([
            'term_related' => 'required|max:50',
            'relation_type_id' => 'required|exists:term_relation_types,id',
        ]);

        $term->relations()->create($request->all());

        return redirect()->route('term-relations.index', $term)
            ->with('success', 'Term relation created successfully.');
    }



    public function edit(Term $term, TermRelation $termRelation)
    {
        $terms = Term::where('id', '!=', $term->id)->get();
        $relationTypes = TermRelationType::all();
        return view('thesaurus.term-relations.edit', compact('term', 'termRelation', 'terms', 'relationTypes'));
    }



    public function update(Request $request, Term $term, TermRelation $termRelation)
    {
        $request->validate([
            'child_id' => 'required|exists:terms,id|different:term_id',
            'relation_type_id' => 'required|exists:term_relation_types,id',
        ]);

        $termRelation->update($request->all());

        return redirect()->route('term-relations.index', $term)
            ->with('success', 'Term relation updated successfully.');
    }



    public function destroy(Term $term, TermRelation $termRelation)
    {
        $termRelation->delete();

        return redirect()->route('term-relations.index', $term)
            ->with('success', 'Term relation deleted successfully.');
    }

    public function show(Term $term, TermRelation $termRelation)
    {
        return view('thesaurus.term-relations.show', compact('term', 'termRelation'));
    }

}
