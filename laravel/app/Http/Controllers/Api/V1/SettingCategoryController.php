<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SettingCategory\StoreSettingCategoryRequest;
use App\Http\Requests\Api\V1\SettingCategory\UpdateSettingCategoryRequest;
use App\Http\Resources\Api\V1\SettingCategoryResource;
use App\Models\SettingCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * D01 — relu et validé le 2026-08-04 contre `SettingCategoryController` et le schéma.
 *
 * Garde du contrôleur Blade conservée : une catégorie qui contient des paramètres
 * ne se supprime pas (409), et un changement de parent créant une référence
 * circulaire est refusé (422).
 */
class SettingCategoryController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'name', 'parent_id', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'name', 'parent_id', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['parent', 'children', 'settings'];

    /**
     * GET /api/v1/setting-categories
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SettingCategory::class);

        $query = SettingCategory::query();

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE);
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, SettingCategoryResource::class));
    }

    /**
     * GET /api/v1/setting-categories/{id}
     */
    public function show(SettingCategory $settingCategory): JsonResponse
    {
        $this->authorize('view', $settingCategory);

        return response()->json(['data' => new SettingCategoryResource($settingCategory)]);
    }

    /**
     * POST /api/v1/setting-categories
     */
    public function store(StoreSettingCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', SettingCategory::class);

        $category = SettingCategory::create($request->validated());

        return response()->json(
            ['data' => new SettingCategoryResource($category)],
            201,
            ['Location' => "/api/v1/setting-categories/{$category->id}"]
        );
    }

    /**
     * PATCH /api/v1/setting-categories/{id}
     */
    public function update(UpdateSettingCategoryRequest $request, SettingCategory $settingCategory): JsonResponse
    {
        $this->authorize('update', $settingCategory);

        if ($request->filled('parent_id') && $this->wouldCreateCircularReference($settingCategory->id, $request->input('parent_id'))) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Validation', 'status' => 422, 'detail' => 'Cette modification créerait une référence circulaire', 'errors' => ['parent_id' => ['Cette modification créerait une référence circulaire']]],
                422
            );
        }

        $settingCategory->update($request->validated());

        return response()->json(['data' => new SettingCategoryResource($settingCategory->fresh())]);
    }

    /**
     * DELETE /api/v1/setting-categories/{id}
     */
    public function destroy(SettingCategory $settingCategory): Response
    {
        $this->authorize('delete', $settingCategory);

        if ($settingCategory->settings()->count() > 0) {
            return response()->json(
                ['type' => 'about:blank', 'title' => 'Conflit d\'intégrité', 'status' => 409, 'detail' => 'Impossible de supprimer une catégorie qui contient des paramètres.'],
                409
            );
        }

        $settingCategory->delete();

        return response()->noContent();
    }

    /**
     * GET /api/v1/setting-categories/tree — arbre hiérarchique complet.
     * Reprend `SettingCategoryController::tree()` (Blade).
     */
    public function tree(): JsonResponse
    {
        $this->authorize('viewAny', SettingCategory::class);

        $categories = SettingCategory::with(['children.children.children', 'settings'])
            ->whereNull('parent_id')
            ->get();

        return response()->json(['data' => SettingCategoryResource::collection($categories)]);
    }

    /**
     * Vérifie si le changement de parent créerait une référence circulaire.
     */
    private function wouldCreateCircularReference(int $categoryId, int $newParentId): bool
    {
        $category = SettingCategory::find($newParentId);

        while ($category) {
            if ($category->id === $categoryId) {
                return true;
            }
            $category = $category->parent;
        }

        return false;
    }
}
