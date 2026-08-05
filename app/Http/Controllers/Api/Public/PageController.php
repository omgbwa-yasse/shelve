<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Public\PageResource;
use App\Models\PublicPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * D15 — pages du portail public. Lecture publique (guard public) : seule la
 * page publiée (`is_published`) est exposée, comme OPAC\PageController.
 */
class PageController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/public/pages — pages publiées, ordonnées comme le portail.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = PublicPage::with('author')->where('is_published', true);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('meta_description', 'like', "%{$search}%");
            });
        }

        $query->orderBy('order')->orderBy('title');

        $page = $query->paginate(min((int) $request->get('per_page', 25), 50))->withQueryString();

        return response()->json($this->paginatedResponse($page, PageResource::class));
    }

    /**
     * GET /api/public/pages/{page} — détail d'une page publiée (404 sinon).
     */
    public function show(PublicPage $page): JsonResponse
    {
        abort_unless($page->is_published, 404);

        return response()->json(['data' => new PageResource($page->load('author'))]);
    }
}
