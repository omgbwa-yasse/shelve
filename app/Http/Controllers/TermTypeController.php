<?php

namespace App\Http\Controllers;

use App\Models\TermType;
use Illuminate\Http\Request;

class TermTypeController extends Controller
{

    public function index()
    {
        $termTypes = TermType::all();
        return view('thesaurus.term-types.index', compact('termTypes'));
    }

    public function create()
    {
        return view('thesaurus.term-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:term_types|max:3',
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        TermType::create($request->all());

        return redirect()->route('term-types.index')->with('success', 'Term type created successfully.');
    }

    public function show(TermType $termType)
    {
        return view('thesaurus.term-types.show', compact('termType'));
    }

    public function edit(TermType $termType)
    {
        return view('thesaurus.term-types.edit', compact('termType'));
    }

    public function update(Request $request, TermType $termType)
    {
        $request->validate([
            'code' => 'required|unique:term_types,code,'.$termType->id.'|max:3',
            'name' => 'required|max:100',
            'description' => 'nullable',
        ]);

        $termType->update($request->all());

        return redirect()->route('term-types.index')->with('success', 'Term type updated successfully.');
    }

    public function destroy(TermType $termType)
    {
        $termType->delete();

        return redirect()->route('term-types.index')->with('success', 'Term type deleted successfully.');
    }
}


