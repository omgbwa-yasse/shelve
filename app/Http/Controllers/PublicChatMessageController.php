<?php

namespace App\Http\Controllers;

use App\Models\PublicChat;
use App\Models\PublicChatMessage;
use App\Models\PublicChatParticipant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicChatMessageController extends Controller
{
    /**
     * Display a listing of the messages for a specific chat.
     *
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function index(PublicChat $publicChat)
    {
        $messages = $publicChat->messages()->with('user')->orderBy('created_at')->get();
        return view('public-chat-messages.index', compact('publicChat', 'messages'));
    }

    /**
     * Store a newly created message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, PublicChat $publicChat)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'user_id' => 'required|exists:public_users,id'
        ]);

        // Verify user is a participant of the chat
        $isParticipant = PublicChatParticipant::where('chat_id', $publicChat->id)
            ->where('user_id', $validated['user_id'])
            ->exists();

        if (!$isParticipant) {
            return back()->with('error', 'You are not a participant of this chat.');
        }

        // Create the message
        $message = $publicChat->messages()->create([
            'user_id' => $validated['user_id'],
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        // Update the last_read_at for the sender
        PublicChatParticipant::where('chat_id', $publicChat->id)
            ->where('user_id', $validated['user_id'])
            ->update(['last_read_at' => now()]);

        return back()->with('success', 'Message sent successfully');
    }

    /**
     * Display the specified message.
     *
     * @param  \App\Models\PublicChat  $publicChat
     * @param  \App\Models\PublicChatMessage  $message
     * @return \Illuminate\Http\Response
     */
    public function show(PublicChat $publicChat, PublicChatMessage $message)
    {
        if ($message->chat_id != $publicChat->id) {
            abort(404);
        }

        return view('public-chat-messages.show', compact('publicChat', 'message'));
    }

    /**
     * Update the specified message in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicChat  $publicChat
     * @param  \App\Models\PublicChatMessage  $message
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicChat $publicChat, PublicChatMessage $message)
    {
        if ($message->chat_id != $publicChat->id) {
            abort(404);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'is_read' => 'boolean'
        ]);

        // Only allow the message owner to update the content
        if ($request->user_id != $message->user_id) {
            return back()->with('error', 'You cannot modify this message.');
        }

        $message->update($validated);

        return back()->with('success', 'Message updated successfully');
    }

    /**
     * Remove the specified message from storage.
     *
     * @param  \App\Models\PublicChat  $publicChat
     * @param  \App\Models\PublicChatMessage  $message
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicChat $publicChat, PublicChatMessage $message)
    {
        if ($message->chat_id != $publicChat->id) {
            abort(404);
        }

        // Only allow message owner or chat admin to delete
        $isAdmin = PublicChatParticipant::where('chat_id', $publicChat->id)
            ->where('user_id', request()->user_id)
            ->where('is_admin', true)
            ->exists();

        if (request()->user_id != $message->user_id && !$isAdmin) {
            return back()->with('error', 'You cannot delete this message.');
        }

        $message->delete();

        return back()->with('success', 'Message deleted successfully');
    }

    /**
     * Mark all messages in a chat as read for a user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function markAsRead(Request $request, PublicChat $publicChat)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:public_users,id'
        ]);

        // Update the last_read_at for the user
        PublicChatParticipant::where('chat_id', $publicChat->id)
            ->where('user_id', $validated['user_id'])
            ->update(['last_read_at' => now()]);

        return back()->with('success', 'All messages marked as read');
    }
}
