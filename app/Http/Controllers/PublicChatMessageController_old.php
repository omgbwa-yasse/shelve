<?php

namespace App\Http\Controllers;

use App\Models\PublicChat;
use App\Models\PublicChatMessage;
use App\Models\PublicChatParticipant;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicChatMessageController extends Controller
{
    /**
     * Display a listing of the messages.
     */
    public function index()
    {
        $messages = PublicChatMessage::with(['chat', 'user'])->latest()->paginate(10);
        return view('public.chat-messages.index', compact('messages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $chats = PublicChat::latest()->get();
        $recentMessages = PublicChatMessage::with('user')->latest()->take(10)->get();
        return view('public.chat-messages.create', compact('chats', 'recentMessages'));
    }

    /**
     * Store a newly created message in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:public_chats,id',
            'type' => 'required|in:text,file,image,system',
            'content' => 'required_if:type,text,system|string|max:5000',
            'file' => 'required_if:type,file,image|file|max:10240', // 10MB max
            'parent_id' => 'nullable|exists:public_chat_messages,id',
        ]);

        $messageData = [
            'chat_id' => $validated['chat_id'],
            'user_id' => auth()->id() ?? 1, // Default user if not authenticated
            'type' => $validated['type'],
            'content' => $validated['content'] ?? '',
            'parent_id' => $validated['parent_id'],
        ];

        // Handle file upload
        if ($request->hasFile('file') && in_array($validated['type'], ['file', 'image'])) {
            $file = $request->file('file');
            $path = $file->store('public/chat-messages');
            $messageData['file_path'] = $path;
            
            if (empty($messageData['content'])) {
                $messageData['content'] = 'Fichier envoyé : ' . $file->getClientOriginalName();
            }
        }

        PublicChatMessage::create($messageData);

        return redirect()->route('public.chat-messages.index')
            ->with('success', 'Message envoyé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicChatMessage $message)
    {
        $message->load(['chat', 'user', 'parent', 'replies.user']);
        return view('public.chat-messages.show', compact('message'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicChatMessage $message)
    {
        $chats = PublicChat::latest()->get();
        $recentMessages = PublicChatMessage::with('user')
            ->where('chat_id', $message->chat_id)
            ->where('id', '!=', $message->id)
            ->latest()->take(10)->get();
        return view('public.chat-messages.edit', compact('message', 'chats', 'recentMessages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicChatMessage $message)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:public_chats,id',
            'type' => 'required|in:text,file,image,system',
            'content' => 'required_if:type,text,system|string|max:5000',
            'file' => 'nullable|file|max:10240', // 10MB max
            'parent_id' => 'nullable|exists:public_chat_messages,id',
        ]);

        $messageData = [
            'chat_id' => $validated['chat_id'],
            'type' => $validated['type'],
            'content' => $validated['content'] ?? $message->content,
            'parent_id' => $validated['parent_id'],
        ];

        // Handle file upload
        if ($request->hasFile('file') && in_array($validated['type'], ['file', 'image'])) {
            // Delete old file if exists
            if ($message->file_path) {
                Storage::delete($message->file_path);
            }
            
            $file = $request->file('file');
            $path = $file->store('public/chat-messages');
            $messageData['file_path'] = $path;
            
            if (empty($messageData['content'])) {
                $messageData['content'] = 'Fichier mis à jour : ' . $file->getClientOriginalName();
            }
        }

        $message->update($messageData);

        return redirect()->route('public.chat-messages.index')
            ->with('success', 'Message modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicChatMessage $message)
    {
        // Delete associated file if exists
        if ($message->file_path) {
            Storage::delete($message->file_path);
        }
        
        $message->delete();

        return redirect()->route('public.chat-messages.index')
            ->with('success', 'Message supprimé avec succès.');
    }
}
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
