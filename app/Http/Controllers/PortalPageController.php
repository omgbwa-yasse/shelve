<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PortalPageController extends Controller
{
    /**
     * Display a listing of the pages.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $pages = Page::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }

    /**
     * Display the specified page.
     *
     * @param Page $page
     * @return JsonResponse
     */
    public function show(Page $page): JsonResponse
    {
        if ($page->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page
        ]);
    }

    /**
     * Get page by slug.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function bySlug(string $slug): JsonResponse
    {
        $page = Page::where('status', 'published')
            ->where('slug', $slug)
            ->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page
        ]);
    }

    /**
     * Search pages.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = Page::where('status', 'published');

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('content')) {
            $query->where('content', 'like', '%' . $request->content . '%');
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $pages = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $pages
        ]);
    }
}
