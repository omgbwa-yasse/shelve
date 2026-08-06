<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AiConversation\StoreAiConversationRequest;
use App\Http\Requests\Api\V1\AiConversation\StoreAiMessageRequest;
use App\Http\Resources\Api\V1\AiConversationResource;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AI\AiAssistantChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Assistant IA (panneau latéral, onglets Chat/Historique) — voir demande
 * utilisateur du 2026-08-05. Ressource strictement personnelle : pas de
 * policy dédiée, l'isolation se fait par `user_id` + organisation (R03 — 404,
 * jamais 403), comme pour les autres ressources purement personnelles.
 */
class AiConversationController extends Controller
{
    /**
     * GET /api/v1/ai/conversations — historique des conversations de l'agent.
     */
    public function index(): JsonResponse
    {
        $conversations = AiConversation::byOrganisation(Auth::user()->current_organisation_id)
            ->where('user_id', Auth::id())
            ->notArchived()
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(['data' => AiConversationResource::collection($conversations)]);
    }

    /**
     * GET /api/v1/ai/conversations/{id} — conversation avec ses messages.
     */
    public function show(AiConversation $conversation): JsonResponse
    {
        $conversation = $this->findOwned($conversation->id)->load('messages');

        return response()->json(['data' => new AiConversationResource($conversation)]);
    }

    /**
     * POST /api/v1/ai/conversations — démarre une conversation avec un premier message.
     */
    public function store(StoreAiConversationRequest $request, AiAssistantChatService $chat): JsonResponse
    {
        $message = $request->validated('message');
        $context = $request->validated('context');
        $mode = $request->validated('mode') ?? AiConversation::MODE_MANUEL;

        $conversation = AiConversation::create([
            'organisation_id' => Auth::user()->current_organisation_id,
            'user_id' => Auth::id(),
            'title' => AiConversation::titleFromMessage($message),
            'context' => $context,
            'mode' => $mode,
        ]);

        $conversation->messages()->create([
            'role' => AiMessage::ROLE_USER,
            'content' => $message,
            'context' => $context,
        ]);

        $result = $chat->reply($message, [], $context, Auth::user(), $mode);

        $conversation->messages()->create([
            'role' => AiMessage::ROLE_ASSISTANT,
            'content' => $result['success'] ? $result['reply'] : $result['error'],
        ]);

        return response()->json(
            ['data' => new AiConversationResource($conversation->fresh('messages'))],
            201,
            ['Location' => "/api/v1/ai/conversations/{$conversation->id}"]
        );
    }

    /**
     * POST /api/v1/ai/conversations/{id}/messages — poursuit une conversation.
     */
    public function sendMessage(StoreAiMessageRequest $request, AiConversation $conversation, AiAssistantChatService $chat): JsonResponse
    {
        $conversation = $this->findOwned($conversation->id);
        $message = $request->validated('message');
        $context = $request->validated('context');
        $mode = $request->validated('mode');

        if ($mode !== null && $mode !== $conversation->mode) {
            $conversation->update(['mode' => $mode]);
        }

        $history = $conversation->messages()
            ->whereIn('role', [AiMessage::ROLE_USER, AiMessage::ROLE_ASSISTANT])
            ->get(['role', 'content'])
            ->map(fn (AiMessage $m) => ['role' => $m->role, 'content' => $m->content])
            ->all();

        $conversation->messages()->create([
            'role' => AiMessage::ROLE_USER,
            'content' => $message,
            'context' => $context,
        ]);

        $result = $chat->reply($message, $history, $context, Auth::user(), $conversation->mode);

        $conversation->messages()->create([
            'role' => AiMessage::ROLE_ASSISTANT,
            'content' => $result['success'] ? $result['reply'] : $result['error'],
        ]);

        $conversation->touch();

        return response()->json(['data' => new AiConversationResource($conversation->fresh('messages'))]);
    }

    /**
     * DELETE /api/v1/ai/conversations/{id} — archive, ne supprime jamais.
     *
     * L'historique des échanges avec l'assistant IA ne doit jamais être
     * effacé (exigence utilisateur du 2026-08-05) : la conversation disparaît
     * de l'onglet Historique (voir `scopeNotArchived`) mais reste en base.
     * Aucune méthode `delete()`/`forceDelete()` n'existe pour ce modèle.
     */
    public function destroy(AiConversation $conversation): Response
    {
        $this->findOwned($conversation->id)->archive();

        return response()->noContent();
    }

    private function findOwned(int $id): AiConversation
    {
        return AiConversation::byOrganisation(Auth::user()->current_organisation_id)
            ->where('user_id', Auth::id())
            ->findOrFail($id);
    }
}
