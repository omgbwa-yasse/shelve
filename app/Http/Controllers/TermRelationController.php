<?php

namespace App\Http\Controllers;

use App\Models\TermRelation;
use Illuminate\Http\Request;


class TermRelationController extends Controller
{


    public function index()
    {
        $termRelations = TermRelation::all();

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

        TermRelation::create($request->all());

        return redirect()->route('term-relations.index')
            ->with('success', 'Term relation created successfully.');
    }





    public function show(TermRelation $termRelation)
    {
        return view('thesaurus.relations.show', compact('termRelation'));
    }





    public function edit(TermRelation $termRelation)
    {
        return view('thesaurus.relations.edit', compact('termRelation'));
    }




    public function update(Request $request, TermRelation $termRelation)
    {
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        $termRelation->update($request->all());

        return redirect()->route('term-relations.index')
            ->with('success', 'Term relation updated successfully');
    }




    public function destroy(TermRelation $termRelation)
    {
        $termRelation->delete();

        return redirect()->route('term-relations.index')
            ->with('success', 'Term relation deleted successfully');
    }
}


