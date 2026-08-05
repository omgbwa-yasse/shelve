<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WorkplaceBookmark\StoreWorkplaceBookmarkRequest;
use App\Http\Resources\Api\V1\WorkplaceBookmarkResource;
use App\Models\Workplace;
use App\Models\WorkplaceBookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * D12 — favoris d'un espace de travail (ressource imbriquée sous `/workplaces`).
 *
 * Relevé contre `WorkplaceBookmarkController` (relu le 2026-08-04). L'accès passe
 * par l'autorisation sur le workplace parent (org-scopé) ; un workplace d'une
 * autre organisation répond 404. `workplace_id` et `user_id` sont posés côté
 * serveur ; le store est un toggle (retire si présent).
 */
class WorkplaceBookmarkController extends Controller
{
    /**
     * GET /api/v1/workplaces/{workplace}/bookmarks
     */
    public function index(Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('view', $workplace);

        $bookmarks = $workplace->bookmarks()
            ->where('user_id', Auth::id())
            ->with('bookmarkable')
            ->latest()
            ->get();

        return response()->json(['data' => WorkplaceBookmarkResource::collection($bookmarks)]);
    }

    /**
     * POST /api/v1/workplaces/{workplace}/bookmarks
     */
    public function store(StoreWorkplaceBookmarkRequest $request, Workplace $workplace): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('view', $workplace);

        $data = $request->validated();

        $bookmark = WorkplaceBookmark::where('workplace_id', $workplace->id)
            ->where('user_id', Auth::id())
            ->where('bookmarkable_type', $data['bookmarkable_type'])
            ->where('bookmarkable_id', $data['bookmarkable_id'])
            ->first();

        if ($bookmark) {
            $bookmark->delete();

            return response()->json(['data' => null, 'deleted' => true]);
        }

        $bookmark = WorkplaceBookmark::create([
            'workplace_id' => $workplace->id,
            'user_id' => Auth::id(),
            'bookmarkable_type' => $data['bookmarkable_type'],
            'bookmarkable_id' => $data['bookmarkable_id'],
            'note' => $data['note'] ?? null,
        ]);

        return response()->json(
            ['data' => new WorkplaceBookmarkResource($bookmark)],
            201,
            ['Location' => "/api/v1/workplaces/{$workplace->id}/bookmarks/{$bookmark->id}"]
        );
    }

    /**
     * DELETE /api/v1/workplaces/{workplace}/bookmarks/{bookmark}
     */
    public function destroy(Workplace $workplace, WorkplaceBookmark $bookmark): JsonResponse
    {
        $workplace = $this->workplaceInOrganisation($workplace);

        $this->authorize('view', $workplace);

        if ($bookmark->user_id !== Auth::id()) {
            abort(403);
        }

        $bookmark->delete();

        return response()->json(['data' => null, 'deleted' => true]);
    }

    private function workplaceInOrganisation(Workplace $workplace): Workplace
    {
        return Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($workplace->id);
    }
}
