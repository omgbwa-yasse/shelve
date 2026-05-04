<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicRecord;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = PublicRecord::available()
            ->with(['record.authors', 'record.thesaurusConcepts', 'publisher']);

        if ($request->filled('q')) {
            $query->searchContent($request->get('q'));
        }

        $books = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('opac.books.index', compact('books'));
    }

    public function show($id)
    {
        $book = PublicRecord::available()
            ->with(['record.authors', 'record.thesaurusConcepts', 'record.attachments', 'publisher'])
            ->findOrFail($id);

        return view('opac.books.show', compact('book'));
    }
}
