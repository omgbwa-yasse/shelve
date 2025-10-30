<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OpacPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PublicPage::query()->orderBy('order', 'asc')->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            }
        }

        $pages = $query->paginate(15)->appends($request->all());

        // Statistics for the dashboard
        $totalPages = PublicPage::count();
        $publishedPages = PublicPage::where('is_published', true)->count();
        $draftPages = PublicPage::where('is_published', false)->count();

        return view('admin.opac.pages.index', compact('pages', 'totalPages', 'publishedPages', 'draftPages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('public.admin.opac.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:public_pages,slug',
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:160',
            'is_published' => 'boolean',
            'order' => 'nullable|integer|min:0'
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        PublicPage::create($validated);

        return redirect()->route('admin.opac.pages.index')->with('success', 'Page créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicPage $page)
    {
        return view('public.admin.opac.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicPage $page)
    {
        return view('public.admin.opac.pages.edit', compact('page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicPage $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:public_pages,slug,' . $page->id,
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:160',
            'is_published' => 'boolean',
            'order' => 'nullable|integer|min:0'
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $page->update($validated);

        return redirect()->route('admin.opac.pages.index')->with('success', 'Page mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicPage $page)
    {
        $page->delete();
        return redirect()->route('admin.opac.pages.index')->with('success', 'Page supprimée avec succès.');
    }

    /**
     * Bulk publish pages
     */
    public function bulkPublish(Request $request)
    {
        $pageIds = $request->get('page_ids', []);
        $action = $request->get('action');

        if (empty($pageIds)) {
            return back()->with('error', 'Aucune page sélectionnée.');
        }

        $count = 0;
        if ($action === 'publish') {
            $count = PublicPage::whereIn('id', $pageIds)->update(['is_published' => true]);
        } elseif ($action === 'unpublish') {
            $count = PublicPage::whereIn('id', $pageIds)->update(['is_published' => false]);
        }

        return back()->with('success', "{$count} page(s) mise(s) à jour.");
    }
}
