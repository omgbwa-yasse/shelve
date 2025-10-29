<?php

namespace App\Http\Controllers\OPAC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of published news
     * Note: News table doesn't exist yet, showing placeholder
     */
    public function index(Request $request)
    {
        // Create empty collection for now
        $news = collect();
        $categories = collect();

        return view('opac.news.index', compact('news', 'categories'));
    }

    /**
     * Display a specific news article
     */
    public function show($id)
    {
        // News table doesn't exist yet
        abort(404, 'News article not found');
    }
}
