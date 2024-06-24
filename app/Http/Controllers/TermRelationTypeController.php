<?php

namespace App\Http\Controllers;

use App\Models\TermRelationType;
use Illuminate\Http\Request;


class TermRelationTypeController extends Controller
{


    public function index()
    {
        $termRelations = TermRelationType::all();

        return view('thesaurus.relations.index', compact('termRelations'));
    }




    public function create()
    {
        return view('thesaurus.relations.create');
    }





    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        TermRelationType::create($request->all());

        return redirect()->route('term-relation-types.index')
            ->with('success', 'Term relation created successfully.');
    }





    public function show(TermRelationType $termRelation)
    {
        return view('thesaurus.relations.show', compact('termRelation'));
    }





    public function edit(TermRelationType $termRelation)
    {
        return view('thesaurus.relations.edit', compact('termRelation'));
    }




    public function update(Request $request, TermRelationType $termRelation)
    {
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        $termRelation->update($request->all());

        return redirect()->route('term-relation-types.index')
            ->with('success', 'Term relation updated successfully');
    }




    public function destroy(TermRelationType $termRelation)
    {
        $termRelation->delete();

        return redirect()->route('term-relation-types.index')
            ->with('success', 'Term relation deleted successfully');
    }
}


