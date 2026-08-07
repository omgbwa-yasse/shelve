<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Public\StoreFeedbackRequest;
use App\Http\Resources\Api\Public\FeedbackResource;
use App\Models\PublicFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * D15 — feedback du portail public. Usager connecté (`auth:sanctum`) :
 * `user_id` est déduit du token (la colonne est NOT NULL en base, aucune
 * soumission anonyme possible — voir le schéma `public_feedbacks`).
 */
class FeedbackController extends Controller
{
    use HandlesApiQueries;

    /**
     * GET /api/public/feedbacks — historique des feedbacks de l'usager connecté.
     */
    public function index(Request $request): JsonResponse
    {
        $page = PublicFeedback::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(min((int) $request->get('per_page', 10), 50))
            ->withQueryString();

        return response()->json($this->paginatedResponse($page, FeedbackResource::class));
    }

    /**
     * POST /api/public/feedbacks — soumission d'un feedback.
     */
    public function store(StoreFeedbackRequest $request): JsonResponse
    {
        $feedback = PublicFeedback::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'status' => 'new',
        ]);

        return response()->json(
            ['data' => new FeedbackResource($feedback)],
            201,
            ['Location' => "/api/public/feedbacks/{$feedback->id}"]
        );
    }
}
