<?php

namespace App\Http\Controllers;

use App\Models\TermEquivalentType;
use Illuminate\Http\Request;


class TermEquivalentTypeController extends Controller
{


    public function index()
    {
        $termEquivalents = TermEquivalentType::all();

        return view('thesaurus.equivalents.index', compact('termEquivalents'));
    }




    public function create()
    {
        return view('thesaurus.equivalents.create');
    }





    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        TermEquivalentType::create($request->all());

        return redirect()->route('term-relation-types.index')
            ->with('success', 'Term relation created successfully.');
    }





    public function show(TermEquivalentType $termEquivalent)
    {
        return view('thesaurus.equivalents.show', compact('termEquivalent'));
    }





    public function edit(TermEquivalentType $termEquivalent)
    {
        return view('thesaurus.equivalents.edit', compact('termEquivalent'));
    }




    public function update(Request $request, TermEquivalentType $termEquivalent)
    {
        $request->validate([
            'code' => 'required|max:10',
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        $termEquivalent->update($request->all());

        return redirect()->route('term-relation-types.index')
            ->with('success', 'Term relation updated successfully');
    }




    public function destroy(TermEquivalentType $termEquivalent)
    {
        $termEquivalent->delete();

        return redirect()->route('term-relation-types.index')
            ->with('success', 'Term relation deleted successfully');
    }
}


