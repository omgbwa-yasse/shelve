<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicChatMessage;
use App\Models\PublicChat;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for Public Chat Messages
 * Manages messages in public chat conversations
 */
class PublicChatMessageApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PublicChatMessage::with(['chat', 'user']);

        // Filter by chat if specified
        if ($request->has('chat_id')) {
            $query->where('chat_id', $request->chat_id);
        }

        // Filter by user if specified
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Search in message content
        if ($request->has('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'last_page' => $messages->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicChatMessage $message): JsonResponse
    {
        $message->load(['chat', 'user']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $message->id,
                'chat_id' => $message->chat_id,
                'user_id' => $message->user_id,
                'message' => $message->message,
                'is_read' => $message->is_read,
                'is_read_label' => $message->is_read ? 'Lu' : 'Non lu',
                'chat' => $message->chat ? [
                    'id' => $message->chat->id,
                    'title' => $message->chat->title,
                    'type' => $message->chat->type,
                ] : null,
                'user' => $message->user ? [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                    'email' => $message->user->email,
                ] : null,
                'created_at' => $message->created_at,
                'updated_at' => $message->updated_at,
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
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $message = PublicChatMessage::create([
                'chat_id' => $request->chat_id,
                'user_id' => $request->user_id,
                'message' => $request->message,
                'is_read' => false,
            ]);

            $message->load(['chat', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Message envoyé avec succès',
                'data' => $message,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicChatMessage $message): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $message->update([
                'message' => $request->message,
            ]);

            $message->load(['chat', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Message modifié avec succès',
                'data' => $message,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicChatMessage $message): JsonResponse
    {
        try {
            $message->delete();

            return response()->json([
                'success' => true,
                'message' => 'Message supprimé avec succès',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get messages by chat.
     */
    public function byChat(PublicChat $chat, Request $request): JsonResponse
    {
        $query = $chat->messages()
            ->with(['user'])
            ->orderBy('created_at', 'asc');

        // Limit the number of messages returned
        $limit = $request->get('limit', 100);
        $messages = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'is_read' => $message->is_read,
                    'user' => $message->user ? [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                    ] : null,
                    'created_at' => $message->created_at,
                ];
            }),
            'total' => $chat->messages()->count(),
        ]);
    }

    /**
     * Get messages by user.
     */
    public function byUser(PublicUser $user, Request $request): JsonResponse
    {
        $query = $user->chatMessages()
            ->with(['chat'])
            ->orderBy('created_at', 'desc');

        $messages = $query->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'last_page' => $messages->lastPage(),
            ],
        ]);
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(PublicChatMessage $message): JsonResponse
    {
        try {
            $message->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Message marqué comme lu',
                'data' => [
                    'is_read' => $message->is_read,
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
     * Mark multiple messages as read.
     */
    public function markMultipleAsRead(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:public_chat_messages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updated = PublicChatMessage::whereIn('id', $request->message_ids)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => "{$updated} message(s) marqué(s) comme lu(s)",
                'data' => [
                    'updated_count' => $updated,
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
     * Get unread messages count by chat.
     */
    public function unreadCount(PublicChat $chat, Request $request): JsonResponse
    {
        $userId = $request->get('user_id');

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'ID utilisateur requis',
            ], 400);
        }

        $unreadCount = $chat->messages()
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'chat_id' => $chat->id,
                'user_id' => $userId,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    /**
     * Search messages across chats.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'chat_id' => 'nullable|exists:public_chats,id',
            'user_id' => 'nullable|exists:public_users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = PublicChatMessage::with(['chat', 'user'])
            ->where('message', 'like', '%' . $request->query . '%');

        if ($request->has('chat_id')) {
            $query->where('chat_id', $request->chat_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'last_page' => $messages->lastPage(),
            ],
            'search_query' => $request->query,
        ]);
    }
}
