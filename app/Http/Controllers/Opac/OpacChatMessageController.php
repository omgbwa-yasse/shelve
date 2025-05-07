<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\OpacChat;
use App\Models\OpacChatMessage;
use App\Models\OpacChatParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OpacChatMessageController extends Controller
{
    /**
     * Display a listing of the messages for a specific chat.
     *
     * @param  \App\Models\OpacChat  $opacChat
     * @return \Illuminate\Http\Response
     */
    public function index(OpacChat $opacChat)
    {
        $messages = $opacChat->messages()->with('user')->orderBy('created_at')->get();
        return response()->json([
            'status' => 'success',
            'message' => 'Chat messages retrieved successfully',
            'data' => $messages
        ], 200);
    }

    /**
     * Store a newly created message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OpacChat  $opacChat
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, OpacChat $opacChat)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'user_id' => 'required|exists:users,id'
        ]);

        // Verify user is a participant of the chat
        $isParticipant = OpacChatParticipant::where('chat_id', $opacChat->id)
            ->where('user_id', $validated['user_id'])
            ->exists();

        if (!$isParticipant) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not a participant of this chat.'
            ], 403);
        }

        // Create the message
        $message = $opacChat->messages()->create([
            'user_id' => $validated['user_id'],
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        // Update the last_read_at for the sender
        OpacChatParticipant::where('chat_id', $opacChat->id)
            ->where('user_id', $validated['user_id'])
            ->update(['last_read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Chat message sent successfully',
            'data' => $message
        ], 201);
    }

    /**
     * Display the specified message.
     *
     * @param  \App\Models\OpacChat  $opacChat
     * @param  \App\Models\OpacChatMessage  $message
     * @return \Illuminate\Http\Response
     */
    public function show(OpacChat $opacChat, OpacChatMessage $message)
    {
        if ($message->chat_id != $opacChat->id) {
            abort(404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Chat message details retrieved successfully',
            'data' => $message->load(['user', 'chat'])
        ], 200);
    }

    /**
     * Update the specified message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OpacChat  $opacChat
     * @param  \App\Models\OpacChatMessage  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OpacChat $opacChat, OpacChatMessage $message)
    {
        if ($message->chat_id != $opacChat->id) {
            abort(404);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'is_read' => 'boolean'
        ]);

        // Only allow the message owner to update the content
        if ($request->user_id != $message->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot modify this message.'
            ], 403);
        }

        $message->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Chat message updated successfully',
            'data' => $message
        ], 200);
    }

    /**
     * Remove the specified message from storage.
     *
     * @param  \App\Models\OpacChat  $opacChat
     * @param  \App\Models\OpacChatMessage  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(OpacChat $opacChat, OpacChatMessage $message)
    {
        if ($message->chat_id != $opacChat->id) {
            abort(404);
        }

        // Only allow message owner or chat admin to delete
        $isAdmin = OpacChatParticipant::where('chat_id', $opacChat->id)
            ->where('user_id', request()->user_id)
            ->where('is_admin', true)
            ->exists();

        if (request()->user_id != $message->user_id && !$isAdmin) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete this message.'
            ], 403);
        }

        $message->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Chat message deleted successfully'
        ], 200);
    }

    /**
     * Mark all messages in a chat as read for a user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OpacChat  $opacChat
     * @return \Illuminate\Http\Response
     */
    public function markAsRead(Request $request, OpacChat $opacChat)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Update the last_read_at for the user
        OpacChatParticipant::where('chat_id', $opacChat->id)
            ->where('user_id', $validated['user_id'])
            ->update(['last_read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'All messages marked as read'
        ], 200);
    }
}
