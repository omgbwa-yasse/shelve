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
        return view('ai.chatmessage.index', compact('messages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.chatmessage.create');
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

        return redirect()->route('ai.chatmessage.index')->with('success', 'AI Chat Message created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiChatMessage  $aiChatMessage
     * @return \Illuminate\Http\Response
     */
    public function show(AiChatMessage $aiChatMessage)
    {
        return view('ai.chatmessage.show', compact('aiChatMessage'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiChatMessage  $aiChatMessage
     * @return \Illuminate\Http\Response
     */
    public function edit(AiChatMessage $aiChatMessage)
    {
        return view('ai.chatmessage.edit', compact('aiChatMessage'));
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

        return redirect()->route('ai.chatmessage.index')->with('success', 'AI Chat Message updated successfully');
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

        return redirect()->route('ai.chatmessage.index')->with('success', 'AI Chat Message deleted successfully');
    }
}
