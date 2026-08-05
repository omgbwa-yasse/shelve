<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Public\TemplateResource;
use App\Models\PublicTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * D15 — templates du portail public.
 *
 * DONNÉES uniquement : cet endpoint expose le contenu brut et les variables des
 * templates actifs. Le RENDU (substitution de variables, moteur de templates,
 * personnalisation) est EXCLU du périmètre API — voir l'en-tête de
 * routes/api/D15.php (R05, repli « OPAC conservé sur Laravel »).
 */
class TemplateController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/public/templates — templates actifs (option `type` :
     * page|email|notification).
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|string|in:page,email,notification',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = PublicTemplate::where('status', 'active');

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        $query->orderBy('name');

        $page = $query->paginate(min((int) $request->get('per_page', 25), 50))->withQueryString();

        return response()->json($this->paginatedResponse($page, TemplateResource::class));
    }

    /**
     * GET /api/public/templates/{template} — détail d'un template actif.
     */
    public function show(PublicTemplate $template): JsonResponse
    {
        abort_unless($template->status === 'active', 404);

        return response()->json(['data' => new TemplateResource($template)]);
    }
}
