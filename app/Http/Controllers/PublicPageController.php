<?php

namespace App\Http\Controllers;

use App\Models\PublicPage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controller for Public Pages
 * Handles static pages administration for the public portal
 */
class PublicPageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:public.pages.manage');
    }

    /**
     * Display a listing of the pages.
     */
    public function index(Request $request)
    {
        $query = PublicPage::with('author')->withCount('children');

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by published status
        if ($request->filled('published')) {
            $isPublished = $request->published === 'yes';
            $query->where('is_published', $isPublished);
        }

        // Order by order field, then by title
        $query->orderBy('order', 'asc')->orderBy('title', 'asc');

        $pages = $query->paginate(15)->appends($request->query());

        // Statistics
        $totalPages = PublicPage::count();
        $publishedPages = PublicPage::where('is_published', true)->count();
        $draftPages = PublicPage::where('status', 'draft')->count();

        // Get available parent pages for create modal
        $parentPages = PublicPage::where('is_published', true)
            ->whereNull('parent_id')
            ->orderBy('title')
            ->get();

        return view('public.pages.index', compact(
            'pages',
            'totalPages',
            'publishedPages', 
            'draftPages',
            'parentPages'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentPages = PublicPage::where('is_published', true)
            ->orderBy('title')
            ->get();

        return view('public.pages.create', compact('parentPages'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'name' => 'nullable|string|max:255|unique:public_pages,name',
            'content' => 'nullable|string',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,private',
            'is_published' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:public_pages,id',
            'image' => 'nullable|image|max:2048', // 2MB max
        ]);

        // Auto-generate name from title if not provided
        if (empty($validated['name'])) {
            $validated['name'] = Str::slug($validated['title']);
            
            // Ensure uniqueness
            $counter = 1;
            $originalName = $validated['name'];
            while (PublicPage::where('name', $validated['name'])->exists()) {
                $validated['name'] = $originalName . '-' . $counter;
                $counter++;
            }
        }

        // Set author
        $validated['author_id'] = auth()->id();

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('public/pages', 'public');
        }

        $page = PublicPage::create($validated);

        if ($request->boolean('save_and_continue')) {
            return redirect()
                ->route('public.pages.edit', $page)
                ->with('success', __('Page created successfully.'));
        }

        return redirect()
            ->route('public.pages.show', $page)
            ->with('success', __('Page created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicPage $page)
    {
        $page->load(['author', 'children', 'parent']);

        return view('public.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicPage $page)
    {
        $parentPages = PublicPage::where('id', '!=', $page->id)
            ->where('is_published', true)
            ->orderBy('title')
            ->get();

        return view('public.pages.edit', compact('page', 'parentPages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicPage $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'name' => 'nullable|string|max:255|unique:public_pages,name,' . $page->id,
            'content' => 'nullable|string',
            'meta_description' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published,private',
            'is_published' => 'boolean',
            'order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:public_pages,id|not_in:' . $page->id,
            'image' => 'nullable|image|max:2048',
            'remove_image' => 'boolean',
        ]);

        // Auto-generate name from title if not provided
        if (empty($validated['name'])) {
            $validated['name'] = Str::slug($validated['title']);
            
            // Ensure uniqueness (exclude current page)
            $counter = 1;
            $originalName = $validated['name'];
            while (PublicPage::where('name', $validated['name'])->where('id', '!=', $page->id)->exists()) {
                $validated['name'] = $originalName . '-' . $counter;
                $counter++;
            }
        }

        // Handle image removal
        if ($request->boolean('remove_image') && $page->image_path) {
            Storage::disk('public')->delete($page->image_path);
            $validated['image_path'] = null;
        }

        // Handle new image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($page->image_path) {
                Storage::disk('public')->delete($page->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('public/pages', 'public');
        }

        $page->update($validated);

        if ($request->boolean('save_and_continue')) {
            return redirect()
                ->route('public.pages.edit', $page)
                ->with('success', __('Page updated successfully.'));
        }

        return redirect()
            ->route('public.pages.show', $page)
            ->with('success', __('Page updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicPage $page)
    {
        // Delete image if exists
        if ($page->image_path) {
            Storage::disk('public')->delete($page->image_path);
        }

        // Update children to become top-level pages
        if ($page->children && $page->children->count() > 0) {
            $page->children()->update(['parent_id' => null]);
        }

        $page->delete();

        return redirect()
            ->route('public.pages.index')
            ->with('success', __('Page deleted successfully.'));
    }

    /**
     * Bulk publish/unpublish pages.
     */
    public function bulkPublish(Request $request)
    {
        $request->validate([
            'page_ids' => 'required|array',
            'page_ids.*' => 'exists:public_pages,id',
            'action' => 'required|in:publish,unpublish',
        ]);

        $pages = PublicPage::whereIn('id', $request->page_ids)->get();
        $isPublished = $request->action === 'publish';

        foreach ($pages as $page) {
            $page->update(['is_published' => $isPublished]);
        }

        $message = $isPublished 
            ? __(':count pages published successfully.', ['count' => $pages->count()])
            : __(':count pages unpublished successfully.', ['count' => $pages->count()]);

        return redirect()
            ->route('public.pages.index')
            ->with('success', $message);
    }

    /**
     * Reorder pages.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'pages' => 'required|array',
            'pages.*.id' => 'required|exists:public_pages,id',
            'pages.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->pages as $pageData) {
            PublicPage::where('id', $pageData['id'])
                ->update(['order' => $pageData['order']]);
        }

        return response()->json(['success' => true]);
    }
}
