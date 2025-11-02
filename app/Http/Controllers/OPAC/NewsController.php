<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use App\Models\PublicNews;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of published news
     */
    public function index(Request $request)
    {
        $query = PublicNews::with('author')
                          ->where('is_published', true)
                          ->orderBy('published_at', 'desc')
                          ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%')
                  ->orWhere('summary', 'like', '%' . $search . '%');
            });
        }

        $news = $query->paginate(12)->appends($request->all());

        // No categories available in current structure
        $categories = collect();

        return view('opac.news.index', compact('news', 'categories'));
    }

    /**
     * Display a specific news article
     */
    public function show($id)
    {
        $article = PublicNews::with('author')
                           ->where('is_published', true)
                           ->findOrFail($id);

        return view('opac.news.show', compact('article'));
    }
}
