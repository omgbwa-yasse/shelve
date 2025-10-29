<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicPage;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display a listing of published pages
     */
    public function index(Request $request)
    {
        $query = PublicPage::where('is_published', true)
                          ->orderBy('order', 'asc')
                          ->orderBy('title', 'asc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%')
                  ->orWhere('meta_description', 'like', '%' . $search . '%');
            });
        }

        $pages = $query->paginate(12)->appends($request->all());

        // No categories available in this table structure
        $categories = collect();

        return view('opac.pages.index', compact('pages', 'categories'));
    }

    /**
     * Display a specific page
     */
    public function show($id)
    {
        $page = PublicPage::where('is_published', true)
                         ->findOrFail($id);

        return view('opac.pages.show', compact('page'));
    }
}
