<?php

namespace App\Http\Controllers;

use App\Models\PublicChat;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicChatController extends Controller
{
    /**
     * Display a listing of the chats.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $chats = PublicChat::with('participants')->get();
        return view('public.chats.index', compact('chats'));
    }

    /**
     * Show the form for creating a new chat.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = PublicUser::all();
        return view('public.chats.create', compact('users'));
    }

    /**
     * Store a newly created chat in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'is_group' => 'boolean',
            'is_active' => 'boolean',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:public_users,id',
        ]);

        // Create the chat
        $chat = PublicChat::create([
            'title' => $validated['title'],
            'is_group' => $validated['is_group'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Attach users to chat
        foreach ($validated['user_ids'] as $userId) {
            $chat->participants()->create([
                'user_id' => $userId,
                'is_admin' => $userId == $request->user_id, // Make creator an admin
                'last_read_at' => now(),
            ]);
        }

        return redirect()->route('public.chats.index')
            ->with('success', 'Chat created successfully');
    }

    /**
     * Display the specified chat.
     *
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function show(PublicChat $chat)
    {
        $chat->load(['messages.user', 'participants.user']);
        return view('public.chats.show', compact('chat'));
    }

    /**
     * Show the form for editing the specified chat.
     *
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function edit(PublicChat $chat)
    {
        $users = PublicUser::all();
        $participants = $chat->participants->pluck('user_id')->toArray();

        return view('public.chats.edit', compact('chat', 'users', 'participants'));
    }

    /**
     * Update the specified chat in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicChat $chat)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'is_group' => 'boolean',
            'is_active' => 'boolean',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:public_users,id',
        ]);

        // Update the chat
        $chat->update([
            'title' => $validated['title'],
            'is_group' => $validated['is_group'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Sync participants
        $existingUserIds = $chat->participants->pluck('user_id')->toArray();
        $newUserIds = $validated['user_ids'];

        // Remove users no longer in the chat
        foreach ($existingUserIds as $userId) {
            if (!in_array($userId, $newUserIds)) {
                $chat->participants()->where('user_id', $userId)->delete();
            }
        }

        // Add new users to the chat
        foreach ($newUserIds as $userId) {
            if (!in_array($userId, $existingUserIds)) {
                $chat->participants()->create([
                    'user_id' => $userId,
                    'is_admin' => false,
                    'last_read_at' => now(),
                ]);
            }
        }

        return redirect()->route('public.chats.index')
            ->with('success', 'Chat updated successfully');
    }

    /**
     * Remove the specified chat from storage.
     *
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicChat $chat)
    {
        $chat->delete();

        return redirect()->route('public.chats.index')
            ->with('success', 'Chat deleted successfully');
    }

    // ========================================
    // API METHODS pour l'interface React
    // ========================================

    /**
     * API: Get user's conversations
     */
    public function apiConversations(Request $request)
    {
        $user = $request->user();

        $conversations = PublicChat::with(['participants', 'messages' => function($query) {
                $query->latest()->limit(1); // Dernier message seulement
            }])
            ->whereHas('participants', function($query) use ($user) {
                $query->where('public_user_id', $user->id);
            })
            ->where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $conversations->items(),
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
    public function apiCreateConversation(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'is_group' => 'boolean',
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:public_users,id',
        ]);

        $user = $request->user();

        // Créer la conversation
        $chat = PublicChat::create([
            'title' => $validated['title'],
            'is_group' => $validated['is_group'] ?? false,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        // Ajouter les participants (inclure le créateur)
        $participantIds = array_unique(array_merge($validated['participant_ids'], [$user->id]));
        $chat->participants()->sync($participantIds);

        $chat->load(['participants']);

        return response()->json([
            'success' => true,
            'message' => 'Conversation created successfully',
            'data' => $chat
        ], 201);
    }

    /**
     * API: Get conversation messages
     */
    public function apiMessages(Request $request, PublicChat $conversation)
    {
        $user = $request->user();

        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->participants()->where('public_user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        $messages = $conversation->messages()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $messages->items(),
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
    public function apiSendMessage(Request $request, PublicChat $conversation)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'message_type' => 'in:text,image,file',
        ]);

        $user = $request->user();

        // Vérifier que l'utilisateur fait partie de la conversation
        if (!$conversation->participants()->where('public_user_id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied'
            ], 403);
        }

        // Créer le message
        $message = $conversation->messages()->create([
            'content' => $validated['content'],
            'message_type' => $validated['message_type'] ?? 'text',
            'user_id' => $user->id,
        ]);

        // Mettre à jour le timestamp de la conversation
        $conversation->touch();

        $message->load(['user']);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message
        ], 201);
    }
}
