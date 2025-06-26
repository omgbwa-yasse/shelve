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
            'user_id' => 1, // Default user for now
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
