<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RecordPeriodical;
use App\Services\RecordPeriodicalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriodicalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of periodicals.
     */
    public function index(Request $request)
    {
        $query = RecordPeriodical::with(['type', 'creator', 'organisation']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('issn', 'like', "%{$search}%");
            });
        }

        $periodicals = $query->latest()->paginate(20);

        return view('periodicals.index', compact('periodicals'));
    }

    /**
     * Display the specified periodical with issues and articles.
     */
    public function show(string $id)
    {
        $periodical = RecordPeriodical::with([
            'type',
            'creator',
            'organisation',
            'issues.articles'
        ])->findOrFail($id);

        return view('periodicals.show', compact('periodical'));
    }

    /**
     * Search articles within periodicals.
     */
    public function articles(Request $request)
    {
        $search = $request->get('search');
        $periodicalId = $request->get('periodical_id');

        $query = \App\Models\RecordPeriodicalArticle::with(['issue.periodical']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('authors', 'like', "%{$search}%")
                  ->orWhere('abstract', 'like', "%{$search}%");
            });
        }

        if ($periodicalId) {
            $query->whereHas('issue', function($q) use ($periodicalId) {
                $q->where('periodical_id', $periodicalId);
            });
        }

        $articles = $query->latest()->paginate(20);

        return view('periodicals.articles', compact('articles', 'search'));
    }
}
