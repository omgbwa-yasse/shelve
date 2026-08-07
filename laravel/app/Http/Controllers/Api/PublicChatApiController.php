<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicChat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * API Controller for Public Chat
 * Handles chat conversations and messaging for the public portal
 */
class PublicChatApiController extends Controller
{
    // Message constants
    private const ACCESS_DENIED = 'Access denied';
    private const CONVERSATION_CREATED = 'Conversation created successfully';
    private const MESSAGE_SENT = 'Message sent successfully';

    // Validation rule constants
    private const REQUIRED_STRING = 'required|string';
    private const REQUIRED_STRING_MAX_255 = 'required|string|max:255';

    private const CREATE_CONVERSATION_RULES = [
        'title' => self::REQUIRED_STRING_MAX_255,
        'is_group' => 'boolean',
        'participant_ids' => 'required|array',
        'participant_ids.*' => 'exists:public_users,id',
    ];

    private const SEND_MESSAGE_RULES = [
        'content' => 'required|string|max:1000',
        'message_type' => 'in:text,image,file',
    ];

    /**
     * API: Get user's conversations
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = PublicChat::with(['participants', 'messages' => function($query) {
                $query->latest()->limit(1); // Only last message
            }])
            ->whereHas('participants', function($query) use ($user) {
                $query->where('public_user_id', $user->id);
            })
            ->where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => collect($conversations->items())->map(function ($conversation) {
                return $this->transformConversation($conversation);
            })->toArray(),
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ]
        ]);
    }

    /**
     * API: Create new conversation
     */
    public function createConversation(Request $request): JsonResponse
    {
        $validated = $request->validate(self::CREATE_CONVERSATION_RULES);
        $user = $request->user();

        // Create conversation
        $chat = PublicChat::create([
            'title' => $validated['title'],
            'is_group' => $validated['is_group'] ?? false,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        // Add participants (include creator)
        $participantIds = array_unique(array_merge($validated['participant_ids'], [$user->id]));
        $chat->participants()->sync($participantIds);

        $chat->load(['participants']);

        return $this->successResponse(
            self::CONVERSATION_CREATED,
            $this->transformConversation($chat),
            201
        );
    }

    /**
     * API: Get conversation messages
     */
    public function messages(Request $request, PublicChat $conversation): JsonResponse
    {
        $user = $request->user();

        // Check if user is part of the conversation
        if (!$conversation->participants()->where('public_user_id', $user->id)->exists()) {
            return $this->errorResponse(self::ACCESS_DENIED, 403);
        }

        $messages = $conversation->messages()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => collect($messages->items())->map(function ($message) {
                return $this->transformMessage($message);
            })->toArray(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ]
        ]);
    }

    /**
     * API: Send message to conversation
     */
    public function sendMessage(Request $request, PublicChat $conversation): JsonResponse
    {
        $validated = $request->validate(self::SEND_MESSAGE_RULES);
        $user = $request->user();

        // Check if user is part of the conversation
        if (!$conversation->participants()->where('public_user_id', $user->id)->exists()) {
            return $this->errorResponse(self::ACCESS_DENIED, 403);
        }

        // Create message
        $message = $conversation->messages()->create([
            'content' => $validated['content'],
            'message_type' => $validated['message_type'] ?? 'text',
            'user_id' => $user->id,
        ]);

        // Update conversation timestamp
        $conversation->touch();

        $message->load(['user']);

        return $this->successResponse(
            self::MESSAGE_SENT,
            $this->transformMessage($message),
            201
        );
    }

    /**
     * Transform conversation data for API response
     */
    private function transformConversation($conversation): array
    {
        $lastMessage = $conversation->messages->first();

        return [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'is_group' => $conversation->is_group,
            'is_active' => $conversation->is_active,
            'created_by' => $conversation->created_by,
            'participants_count' => $conversation->participants ? $conversation->participants->count() : 0,
            'participants' => $conversation->participants ?
                $conversation->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'name' => $participant->name,
                        'first_name' => $participant->first_name,
                        'full_name' => trim($participant->first_name . ' ' . $participant->name),
                        'email' => $participant->email,
                    ];
                }) : [],
            'last_message' => $lastMessage ? $this->transformMessage($lastMessage) : null,
            'created_at' => $conversation->created_at?->toISOString(),
            'updated_at' => $conversation->updated_at?->toISOString(),
            'formatted_updated_at' => $conversation->updated_at ?
                $conversation->updated_at->format('d/m/Y H:i') : null,
        ];
    }

    /**
     * Transform message data for API response
     */
    private function transformMessage($message): array
    {
        return [
            'id' => $message->id,
            'content' => $message->content,
            'message_type' => $message->message_type ?? 'text',
            'user_id' => $message->user_id,
            'chat_id' => $message->chat_id,
            'user' => $message->user ? [
                'id' => $message->user->id,
                'name' => $message->user->name,
                'first_name' => $message->user->first_name,
                'full_name' => trim($message->user->first_name . ' ' . $message->user->name),
                'email' => $message->user->email,
            ] : null,
            'created_at' => $message->created_at?->toISOString(),
            'updated_at' => $message->updated_at?->toISOString(),
            'formatted_created_at' => $message->created_at ?
                $message->created_at->format('d/m/Y H:i') : null,
        ];
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
