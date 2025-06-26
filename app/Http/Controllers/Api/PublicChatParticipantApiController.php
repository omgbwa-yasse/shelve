<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicChatParticipant;
use App\Models\PublicChat;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for Public Chat Participants
 * Manages participants in public chat conversations
 */
class PublicChatParticipantApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PublicChatParticipant::with(['chat', 'user']);

        // Filter by chat if specified
        if ($request->has('chat_id')) {
            $query->where('chat_id', $request->chat_id);
        }

        // Filter by user if specified
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by admin status
        if ($request->has('is_admin')) {
            $query->where('is_admin', $request->boolean('is_admin'));
        }

        $participants = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $participants->items(),
            'pagination' => [
                'current_page' => $participants->currentPage(),
                'per_page' => $participants->perPage(),
                'total' => $participants->total(),
                'last_page' => $participants->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicChatParticipant $participant): JsonResponse
    {
        $participant->load(['chat', 'user']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $participant->id,
                'chat_id' => $participant->chat_id,
                'user_id' => $participant->user_id,
                'is_admin' => $participant->is_admin,
                'is_admin_label' => $participant->is_admin ? 'Administrateur' : 'Participant',
                'last_read_at' => $participant->last_read_at,
                'chat' => $participant->chat ? [
                    'id' => $participant->chat->id,
                    'title' => $participant->chat->title,
                    'type' => $participant->chat->type,
                ] : null,
                'user' => $participant->user ? [
                    'id' => $participant->user->id,
                    'name' => $participant->user->name,
                    'email' => $participant->user->email,
                ] : null,
                'created_at' => $participant->created_at,
                'updated_at' => $participant->updated_at,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|exists:public_chats,id',
            'user_id' => 'required|exists:public_users,id',
            'is_admin' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Check if participant already exists
            $existingParticipant = PublicChatParticipant::where('chat_id', $request->chat_id)
                ->where('user_id', $request->user_id)
                ->first();

            if ($existingParticipant) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'utilisateur est déjà participant de cette conversation',
                ], 409);
            }

            $participant = PublicChatParticipant::create([
                'chat_id' => $request->chat_id,
                'user_id' => $request->user_id,
                'is_admin' => $request->boolean('is_admin', false),
                'last_read_at' => null,
            ]);

            $participant->load(['chat', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Participant ajouté avec succès',
                'data' => $participant,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du participant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicChatParticipant $participant): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'is_admin' => 'boolean',
            'last_read_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $participant->update($request->only(['is_admin', 'last_read_at']));
            $participant->load(['chat', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Participant mis à jour avec succès',
                'data' => $participant,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du participant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicChatParticipant $participant): JsonResponse
    {
        try {
            $participant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Participant retiré avec succès',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du participant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get participants by chat.
     */
    public function byChat(PublicChat $chat): JsonResponse
    {
        $participants = $chat->participants()
            ->with(['user'])
            ->orderBy('is_admin', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'user_id' => $participant->user_id,
                    'is_admin' => $participant->is_admin,
                    'is_admin_label' => $participant->is_admin ? 'Administrateur' : 'Participant',
                    'last_read_at' => $participant->last_read_at,
                    'user' => $participant->user ? [
                        'id' => $participant->user->id,
                        'name' => $participant->user->name,
                        'email' => $participant->user->email,
                    ] : null,
                    'created_at' => $participant->created_at,
                ];
            }),
        ]);
    }

    /**
     * Get chats by user.
     */
    public function byUser(PublicUser $user): JsonResponse
    {
        $participants = $user->chatParticipations()
            ->with(['chat'])
            ->orderBy('last_read_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $participants->map(function ($participant) {
                return [
                    'id' => $participant->id,
                    'chat_id' => $participant->chat_id,
                    'is_admin' => $participant->is_admin,
                    'is_admin_label' => $participant->is_admin ? 'Administrateur' : 'Participant',
                    'last_read_at' => $participant->last_read_at,
                    'chat' => $participant->chat ? [
                        'id' => $participant->chat->id,
                        'title' => $participant->chat->title,
                        'type' => $participant->chat->type,
                        'created_at' => $participant->chat->created_at,
                    ] : null,
                    'created_at' => $participant->created_at,
                ];
            }),
        ]);
    }

    /**
     * Mark messages as read for a participant.
     */
    public function markAsRead(PublicChatParticipant $participant): JsonResponse
    {
        try {
            $participant->update([
                'last_read_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Messages marqués comme lus',
                'data' => [
                    'last_read_at' => $participant->last_read_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle admin status for a participant.
     */
    public function toggleAdmin(PublicChatParticipant $participant): JsonResponse
    {
        try {
            $participant->update([
                'is_admin' => !$participant->is_admin,
            ]);

            return response()->json([
                'success' => true,
                'message' => $participant->is_admin ?
                    'Utilisateur promu administrateur' :
                    'Droits d\'administrateur retirés',
                'data' => [
                    'is_admin' => $participant->is_admin,
                    'is_admin_label' => $participant->is_admin ? 'Administrateur' : 'Participant',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
