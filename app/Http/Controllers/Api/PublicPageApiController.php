<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicPage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for Public Pages
 * Handles static pages management for the public portal
 */
class PublicPageApiController extends Controller
{
    // Message constants
    private const PAGE_CREATED = 'Page created successfully';
    private const PAGE_UPDATED = 'Page updated successfully';
    private const PAGE_DELETED = 'Page deleted successfully';
    private const PAGE_NOT_FOUND = 'Page not found';

    // Validation rule constants
    private const REQUIRED_STRING = 'required|string';
    private const REQUIRED_STRING_MAX_255 = 'required|string|max:255';
    private const NULLABLE_STRING = 'nullable|string';

    private const STORE_RULES = [
        'title' => self::REQUIRED_STRING_MAX_255,
        'slug' => 'required|string|max:255|unique:public_pages',
        'content' => self::REQUIRED_STRING,
        'meta_description' => self::NULLABLE_STRING,
        'meta_keywords' => self::NULLABLE_STRING,
        'status' => 'required|in:draft,published,archived',
        'featured_image_path' => self::NULLABLE_STRING,
    ];

    private const UPDATE_RULES = [
        'title' => 'sometimes|required|string|max:255',
        'slug' => 'sometimes|required|string|max:255',
        'content' => 'sometimes|required|string',
        'meta_description' => self::NULLABLE_STRING,
        'meta_keywords' => self::NULLABLE_STRING,
        'status' => 'sometimes|required|in:draft,published,archived',
        'featured_image_path' => self::NULLABLE_STRING,
    ];

    /**
     * Get paginated pages for frontend
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published,archived',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = PublicPage::with(['author']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Default sorting: published pages first, then by creation date
        $query->orderByRaw("CASE WHEN status = 'published' THEN 1 ELSE 2 END")
              ->orderBy('created_at', 'desc');

        // Pagination
        $perPage = min($request->get('per_page', 10), 50);
        $pages = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($pages->items())->map(function ($page) {
                return $this->transformPage($page);
            })->toArray(),
            'pagination' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
                'from' => $pages->firstItem(),
                'to' => $pages->lastItem(),
            ]
        ]);
    }

    /**
     * Get single page details
     */
    public function show($id): JsonResponse
    {
        $page = PublicPage::with(['author'])->find($id);

        if (!$page) {
            return $this->errorResponse(self::PAGE_NOT_FOUND, 404);
        }

        return $this->successResponse('Page retrieved successfully', $this->transformPage($page));
    }

    /**
     * Get page by slug (for public frontend)
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $page = PublicPage::with(['author'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$page) {
            return $this->errorResponse(self::PAGE_NOT_FOUND, 404);
        }

        return $this->successResponse('Page retrieved successfully', $this->transformPage($page));
    }

    /**
     * Store new page
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(self::STORE_RULES);

        // Set author if authenticated
        if ($request->user()) {
            $validated['author_id'] = $request->user()->id;
        }

        $page = PublicPage::create($validated);
        $page->load(['author']);

        return $this->successResponse(
            self::PAGE_CREATED,
            $this->transformPage($page),
            201
        );
    }

    /**
     * Update existing page
     */
    public function update(Request $request, $id): JsonResponse
    {
        $page = PublicPage::find($id);

        if (!$page) {
            return $this->errorResponse(self::PAGE_NOT_FOUND, 404);
        }

        $rules = self::UPDATE_RULES;
        // Add unique validation for slug if it's being updated
        if ($request->has('slug')) {
            $rules['slug'] = 'sometimes|required|string|max:255|unique:public_pages,slug,' . $id;
        }

        $validated = $request->validate($rules);
        $page->update($validated);
        $page->load(['author']);

        return $this->successResponse(
            self::PAGE_UPDATED,
            $this->transformPage($page->fresh())
        );
    }

    /**
     * Delete page
     */
    public function destroy($id): JsonResponse
    {
        $page = PublicPage::find($id);

        if (!$page) {
            return $this->errorResponse(self::PAGE_NOT_FOUND, 404);
        }

        $page->delete();

        return $this->successResponse(self::PAGE_DELETED);
    }

    /**
     * Get published pages for public display
     */
    public function published(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = PublicPage::with(['author'])
            ->where('status', 'published');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $perPage = min($request->get('per_page', 10), 50);
        $pages = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($pages->items())->map(function ($page) {
                return $this->transformPage($page, true); // Public view
            })->toArray(),
            'pagination' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
            ]
        ]);
    }

    /**
     * Transform page data for API response
     */
    private function transformPage($page, bool $publicView = false): array
    {
        $data = [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'meta_description' => $page->meta_description,
            'meta_keywords' => $page->meta_keywords,
            'status' => $page->status,
            'status_label' => $this->getStatusLabel($page->status),
            'featured_image_path' => $page->featured_image_path,
            'featured_image_url' => $page->featured_image_path ?
                asset('storage/' . $page->featured_image_path) : null,
            'author' => $page->author ? [
                'id' => $page->author->id,
                'name' => $page->author->name,
                'email' => $publicView ? null : $page->author->email, // Hide email in public view
            ] : null,
            'created_at' => $page->created_at?->toISOString(),
            'updated_at' => $page->updated_at?->toISOString(),
            'formatted_created_at' => $page->created_at ?
                $page->created_at->format('d/m/Y H:i') : null,
        ];

        // Remove admin fields for public view
        if ($publicView) {
            unset($data['author']['email']);
        }

        return $data;
    }

    /**
     * Get status label
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'published' => 'Publié',
            'archived' => 'Archivé',
            default => 'Statut inconnu'
        };
    }

    /**
     * Success response helper
     */
    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
