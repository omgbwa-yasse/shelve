<?php

namespace App\Http\Controllers;

use App\Models\PublicPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = PublicPage::orderBy('title')->paginate(10);
        return view('public.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('public.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:public_pages',
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('public/pages');
            $validated['featured_image_path'] = $path;
        }

        $validated['author_id'] = auth()->id();
        $page = PublicPage::create($validated);

        return redirect()->route('public.pages.show', $page)
            ->with('success', 'Page created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicPage $page)
    {
        return view('public.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicPage $page)
    {
        return view('public.pages.edit', compact('page'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicPage $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:public_pages,slug,' . $page->id,
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'featured_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($page->featured_image_path) {
                Storage::delete($page->featured_image_path);
            }
            $path = $request->file('featured_image')->store('public/pages');
            $validated['featured_image_path'] = $path;
        }

        $page->update($validated);

        return redirect()->route('public.pages.show', $page)
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicPage $page)
    {
        if ($page->featured_image_path) {
            Storage::delete($page->featured_image_path);
        }

        $page->delete();

        return redirect()->route('public.pages.index')
            ->with('success', 'Page deleted successfully.');
    }

    /**
     * Update the status of the page.
     */
    public function updateStatus(Request $request, PublicPage $page)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,published,archived'
        ]);

        $page->update($validated);

        return redirect()->back()
            ->with('success', 'Status updated successfully.');
    }
}
