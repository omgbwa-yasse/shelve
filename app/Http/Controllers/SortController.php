<?php

namespace App\Http\Controllers;

use App\models\Sort;
use Illuminate\Http\Request;


class SortController extends Controller
{
    public function index()
    {
        $sorts = Sort::all();
        return view('sorts.index', compact('sorts'));
    }

    public function create()
    {
        return view('sorts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:sorts|in:E,T,C',
            'name' => 'required|max:45',
            'description' => 'nullable|max:100',
        ]);

        Sort::create($request->all());

        return redirect()->route('sorts.index')
            ->with('success', 'Sort created successfully.');
    }

    public function show(Sort $sort)
    {
        return view('sorts.show', compact('sort'));
    }

    public function edit(Sort $sort)
    {
        return view('sorts.edit', compact('sort'));
    }

    public function update(Request $request, Sort $sort)
    {
        $request->validate([
            'code' => 'required|unique:sorts,code,' . $sort->id . '|in:E,T,C',
            'name' => 'required|max:45',
            'description' => 'nullable|max:100',
        ]);

        $sort->update($request->all());

        return redirect()->route('sorts.index')
            ->with('success', 'Sort updated successfully.');
    }

    public function destroy(Sort $sort)
    {
        $sort->delete();

        return redirect()->route('sorts.index')
            ->with('success', 'Sort deleted successfully.');
    }
}

