<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesApiQueries;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\WorkplaceConversation\StoreWorkplaceConversationRequest;
use App\Http\Requests\Api\V1\WorkplaceConversation\UpdateWorkplaceConversationRequest;
use App\Http\Resources\Api\V1\WorkplaceConversationResource;
use App\Http\Resources\Api\V1\WorkplaceMessageResource;
use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceConversation;
use App\Models\WorkplaceConversationParticipant;
use App\Models\WorkplaceMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * D12 — conversations et messages (chats + messages d'espace de travail).
 *
 * Fusionne la logique de `ChatController` et de `WorkplaceMessageController`
 * (relus le 2026-08-04) : les deux manipulent le modèle WorkplaceConversation,
 * `workplace_id` étant NULL pour les conversations globales. L'accès est limité
 * aux participants (403) ; le créateur seul peut supprimer/renommer. Un workplace
 * hors organisation courante répond 404.
 */
class WorkplaceConversationController extends Controller
{
    use HandlesApiQueries;

    private const FILTERABLE = ['id', 'workplace_id', 'type', 'name', 'created_by', 'created_at', 'updated_at'];
    private const SORTABLE = ['id', 'workplace_id', 'type', 'name', 'created_by', 'created_at', 'updated_at'];
    private const INCLUDABLE = ['creator', 'participants.user', 'workplace', 'messages.user'];

    /**
     * GET /api/v1/workplace-conversations
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkplaceConversation::class);

        if ($request->filled('workplace_id')) {
            // Isolation : un workplace hors organisation courante est 404.
            Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($request->input('workplace_id'));
        }

        $query = WorkplaceConversation::whereHas('participants', fn ($q) => $q->where('user_id', Auth::id()));

        $this->applyFilters($query, $request, self::FILTERABLE);
        $this->applySorting($query, $request, self::SORTABLE, 'updated_at');
        $this->applyIncludes($query, $request, self::INCLUDABLE);

        $page = $query->paginate($this->pageSize($request))->withQueryString();

        return response()->json($this->paginatedResponse($page, WorkplaceConversationResource::class));
    }

    /**
     * GET /api/v1/workplace-conversations/{id}
     */
    public function show(WorkplaceConversation $workplaceConversation): JsonResponse
    {
        $this->authorize('view', $workplaceConversation);

        if (!$workplaceConversation->isParticipant(Auth::id())) {
            abort(403);
        }

        $workplaceConversation->participants()->where('user_id', Auth::id())->update(['last_read_at' => now()]);

        $workplaceConversation->load(['creator', 'participants.user', 'messages.user', 'workplace']);

        return response()->json(['data' => new WorkplaceConversationResource($workplaceConversation)]);
    }

    /**
     * POST /api/v1/workplace-conversations
     */
    public function store(StoreWorkplaceConversationRequest $request): JsonResponse
    {
        $this->authorize('create', WorkplaceConversation::class);

        $data = $request->validated();

        if (!empty($data['workplace_id'])) {
            Workplace::byOrganisation(Auth::user()->current_organisation_id)->findOrFail($data['workplace_id']);
        }

        // Conversation privée : réutiliser une existante entre les deux membres.
        if ($data['type'] === 'private' && count($data['participant_ids']) === 1) {
            $otherId = (int) $data['participant_ids'][0];

            $existing = WorkplaceConversation::where('workplace_id', $data['workplace_id'] ?? null)
                ->where('type', 'private')
                ->whereHas('participants', fn ($q) => $q->where('user_id', Auth::id()))
                ->whereHas('participants', fn ($q) => $q->where('user_id', $otherId))
                ->get()
                ->first(fn ($c) => $c->participants()->count() === 2);

            if ($existing) {
                return response()->json(['data' => new WorkplaceConversationResource($existing)]);
            }
        }

        $conversation = WorkplaceConversation::create([
            'workplace_id' => $data['workplace_id'] ?? null,
            'type' => $data['type'],
            'name' => $data['type'] === 'private' ? null : $data['name'],
            'created_by' => Auth::id(),
        ]);

        $participantIds = array_values(array_unique(array_merge([Auth::id()], $data['participant_ids'])));

        foreach ($participantIds as $userId) {
            WorkplaceConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => (int) $userId,
                'role' => (int) $userId === Auth::id() ? 'owner' : 'member',
            ]);
        }

        return response()->json(
            ['data' => new WorkplaceConversationResource($conversation->fresh(['creator', 'participants.user']))],
            201,
            ['Location' => "/api/v1/workplace-conversations/{$conversation->id}"]
        );
    }

    /**
     * PATCH /api/v1/workplace-conversations/{id}
     */
    public function update(UpdateWorkplaceConversationRequest $request, WorkplaceConversation $workplaceConversation): JsonResponse
    {
        $this->authorize('update', $workplaceConversation);

        if (Auth::id() !== $workplaceConversation->created_by) {
            abort(403);
        }

        $workplaceConversation->update($request->validated());

        return response()->json(['data' => new WorkplaceConversationResource($workplaceConversation->fresh())]);
    }

    /**
     * DELETE /api/v1/workplace-conversations/{id}
     */
    public function destroy(WorkplaceConversation $workplaceConversation): Response
    {
        $this->authorize('delete', $workplaceConversation);

        if (Auth::id() !== $workplaceConversation->created_by) {
            abort(403);
        }

        $workplaceConversation->delete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/workplace-conversations/{id}/messages
     */
    public function storeMessage(Request $request, WorkplaceConversation $workplaceConversation): JsonResponse
    {
        $this->authorize('view', $workplaceConversation);

        if (!$workplaceConversation->isParticipant(Auth::id())) {
            abort(403);
        }

        $request->validate(['content' => 'required|string']);

        $message = WorkplaceMessage::create([
            'conversation_id' => $workplaceConversation->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        $workplaceConversation->touch();

        return response()->json(
            ['data' => new WorkplaceMessageResource($message)],
            201,
            ['Location' => "/api/v1/workplace-conversations/{$workplaceConversation->id}/messages/{$message->id}"]
        );
    }
}
