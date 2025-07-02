<?php

namespace App\Http\Controllers;

use App\Models\AiChatMessage;
use Illuminate\Http\Request;

class AiChatMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $messages = AiChatMessage::with(['chat'])->paginate(15);
        return view('ai.chats.messages.index', compact('messages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.chats.messages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ai_chat_id' => 'required|exists:ai_chats,id',
            'role' => 'required|string',
            'content' => 'required|string',
            'metadata' => 'nullable|json',
        ]);

        AiChatMessage::create($validated);

        return redirect()->route('ai.chats.messages.index')->with('success', 'AI Chat Message created successfully');
    }

    /**
     * Store a message for a specific chat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $chatId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeForChat(Request $request, $chatId)
    {
        $chat = \App\Models\AiChat::findOrFail($chatId);

        $validated = $request->validate([
            'content' => 'required|string',
            'role' => 'required|string',
        ]);

        // Ajouter l'ID du chat
        $validated['ai_chat_id'] = $chat->id;

        // Créer le message
        $message = AiChatMessage::create($validated);

        // Si c'est un message utilisateur, générer une réponse de l'IA
        if ($validated['role'] === 'user') {
            // Créer une réponse de l'assistant (à remplacer par une vraie intégration d'IA)
            AiChatMessage::create([
                'ai_chat_id' => $chat->id,
                'role' => 'assistant',
                'content' => 'Merci pour votre message. Je suis en train de traiter votre demande.',
                'metadata' => [
                    'type' => 'response',
                    'timestamp' => now()->timestamp
                ]
            ]);
        }

        return redirect()->route('ai.chats.show', ['chat' => $chatId])->with('success', 'Message envoyé avec succès !');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiChatMessage  $aiChatMessage
     * @return \Illuminate\Http\Response
     */
    public function show(AiChatMessage $aiChatMessage)
    {
        return view('ai.chats.messages.show', compact('aiChatMessage'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiChatMessage  $aiChatMessage
     * @return \Illuminate\Http\Response
     */
    public function edit(AiChatMessage $aiChatMessage)
    {
        return view('ai.chats.messages.edit', compact('aiChatMessage'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiChatMessage  $aiChatMessage
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiChatMessage $aiChatMessage)
    {
        $validated = $request->validate([
            'ai_chat_id' => 'required|exists:ai_chats,id',
            'role' => 'required|string',
            'content' => 'required|string',
            'metadata' => 'nullable|json',
        ]);

        $aiChatMessage->update($validated);

        return redirect()->route('ai.chats.messages.index')->with('success', 'AI Chat Message updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiChatMessage  $aiChatMessage
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiChatMessage $aiChatMessage)
    {
        $aiChatMessage->delete();

        return redirect()->route('ai.chats.messages.index')->with('success', 'AI Chat Message deleted successfully');
    }
}
