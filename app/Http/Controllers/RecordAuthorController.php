<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\AuthorType;
use Illuminate\Http\Request;

class RecordAuthorController extends Controller
{

    public function index()
    {
        $authors = Author::all();
        return view('records.authors.index', compact('authors'));
    }


    public function create()
    {
        $authorTypes = AuthorType::all();
        $parents = author::all();
        $parents->load('authorType');
        return view('records.authors.create', compact('authorTypes','parents'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:author_types,id',
            'name' => 'required|string|max:255',
            'parallel_name' => 'nullable|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'lifespan' => 'nullable|string|max:255',
            'locations' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:authors,id',
        ]);

        Author::create($request->all());

        return redirect()->route('record-author.index')->with('success', 'Author created successfully.');
    }


    public function show(Author $author)
    {
        $author->load('contacts');
        return view('records.authors.show', compact('author'));
    }


    public function edit(Author $author)
    {
        return view('records.authors.edit', compact('author'));
    }


    public function update(Request $request, Author $author)
    {
        $request->validate([
            'type_id' => 'required|exists:author_types,id',
            'name' => 'required|string|max:255',
            'parallel_name' => 'nullable|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'lifespan' => 'nullable|string|max:255',
            'locations' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:authors,id',
        ]);

        $author->update($request->all());

        return redirect()->route('record-author.index')->with('success', 'Author updated successfully');
    }


    public function destroy(Author $author)
    {
        $author->delete();

        return redirect()->route('record-author.index')->with('success', 'Author deleted successfully');
    }
}
