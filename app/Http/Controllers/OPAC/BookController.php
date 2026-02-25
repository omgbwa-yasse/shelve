<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\RecordBook;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = RecordBook::query()
            ->where('status', 'active')
            ->where('access_level', 'public')
            ->with(['authors', 'publisher']);

        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhereHas('authors', function($q) use ($search) {
                      $q->where('last_name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%");
                  });
            });
        }

        $books = $query->latest()->paginate(12);

        return view('opac.books.index', compact('books'));
    }

    public function show($id)
    {
        $book = RecordBook::where('status', 'active')
            ->where('access_level', 'public')
            ->with(['authors', 'publisher', 'series', 'subjects'])
            ->findOrFail($id);

        return view('opac.books.show', compact('book'));
    }
}
