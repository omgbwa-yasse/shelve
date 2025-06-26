<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for Public Templates
 * Handles template management for the public portal
 */
class PublicTemplateApiController extends Controller
{
    // Message constants
    private const TEMPLATE_CREATED = 'Template created successfully';
    private const TEMPLATE_UPDATED = 'Template updated successfully';
    private const TEMPLATE_DELETED = 'Template deleted successfully';
    private const TEMPLATE_NOT_FOUND = 'Template not found';

    // Validation rule constants
    private const REQUIRED_STRING = 'required|string';
    private const REQUIRED_STRING_MAX_255 = 'required|string|max:255';
    private const NULLABLE_STRING = 'nullable|string';

    private const STORE_RULES = [
        'name' => self::REQUIRED_STRING_MAX_255,
        'description' => self::NULLABLE_STRING,
        'type' => 'required|in:email,document,notification,form',
        'content' => self::REQUIRED_STRING,
        'variables' => 'nullable|array',
        'status' => 'required|in:draft,active,archived',
    ];

    private const UPDATE_RULES = [
        'name' => 'sometimes|required|string|max:255',
        'description' => self::NULLABLE_STRING,
        'type' => 'sometimes|required|in:email,document,notification,form',
        'content' => 'sometimes|required|string',
        'variables' => 'nullable|array',
        'status' => 'sometimes|required|in:draft,active,archived',
    ];

    /**
     * Get paginated templates
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'type' => 'nullable|in:email,document,notification,form',
            'status' => 'nullable|in:draft,active,archived',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = PublicTemplate::with(['author']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $query->orderBy('created_at', 'desc');

        $perPage = min($request->get('per_page', 10), 50);
        $templates = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($templates->items())->map(function ($template) {
                return $this->transformTemplate($template);
            })->toArray(),
            'pagination' => [
                'current_page' => $templates->currentPage(),
                'last_page' => $templates->lastPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
            ]
        ]);
    }

    /**
     * Get single template
     */
    public function show($id): JsonResponse
    {
        $template = PublicTemplate::with(['author'])->find($id);

        if (!$template) {
            return $this->errorResponse(self::TEMPLATE_NOT_FOUND, 404);
        }

        return $this->successResponse('Template retrieved successfully', $this->transformTemplate($template));
    }

    /**
     * Store new template
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(self::STORE_RULES);

        if ($request->user()) {
            $validated['author_id'] = $request->user()->id;
        }

        $template = PublicTemplate::create($validated);
        $template->load(['author']);

        return $this->successResponse(
            self::TEMPLATE_CREATED,
            $this->transformTemplate($template),
            201
        );
    }

    /**
     * Update template
     */
    public function update(Request $request, $id): JsonResponse
    {
        $template = PublicTemplate::find($id);

        if (!$template) {
            return $this->errorResponse(self::TEMPLATE_NOT_FOUND, 404);
        }

        $validated = $request->validate(self::UPDATE_RULES);
        $template->update($validated);
        $template->load(['author']);

        return $this->successResponse(
            self::TEMPLATE_UPDATED,
            $this->transformTemplate($template->fresh())
        );
    }

    /**
     * Delete template
     */
    public function destroy($id): JsonResponse
    {
        $template = PublicTemplate::find($id);

        if (!$template) {
            return $this->errorResponse(self::TEMPLATE_NOT_FOUND, 404);
        }

        $template->delete();

        return $this->successResponse(self::TEMPLATE_DELETED);
    }

    /**
     * Get active templates by type
     */
    public function byType(string $type): JsonResponse
    {
        $templates = PublicTemplate::where('type', $type)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $templates->map(function ($template) {
                return $this->transformTemplate($template, true); // Light version
            })
        ]);
    }

    /**
     * Transform template data for API response
     */
    private function transformTemplate($template, bool $lightVersion = false): array
    {
        $data = [
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'type' => $template->type,
            'type_label' => $this->getTypeLabel($template->type),
            'status' => $template->status,
            'status_label' => $this->getStatusLabel($template->status),
            'variables' => $template->variables ?? [],
            'variables_count' => is_array($template->variables) ? count($template->variables) : 0,
        ];

        if (!$lightVersion) {
            $data = array_merge($data, [
                'content' => $template->content,
                'author' => $template->author ? [
                    'id' => $template->author->id,
                    'name' => $template->author->name,
                ] : null,
                'created_at' => $template->created_at?->toISOString(),
                'updated_at' => $template->updated_at?->toISOString(),
                'formatted_created_at' => $template->created_at ?
                    $template->created_at->format('d/m/Y H:i') : null,
            ]);
        }

        return $data;
    }

    /**
     * Get type label
     */
    private function getTypeLabel(string $type): string
    {
        return match ($type) {
            'email' => 'Email',
            'document' => 'Document',
            'notification' => 'Notification',
            'form' => 'Formulaire',
            default => 'Type inconnu'
        };
    }

    /**
     * Get status label
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Brouillon',
            'active' => 'Actif',
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
