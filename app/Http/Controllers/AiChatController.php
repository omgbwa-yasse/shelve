<?php

namespace App\Http\Controllers;

use App\Models\AiChat;
use App\Models\AiModel;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $chats = AiChat::with(['user', 'aiModel'])->paginate(15);
        return view('ai.chats.index', compact('chats'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('ai.chats.create', [
            'aiModels' => AiModel::all(),
        ]);
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
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'ai_model_id' => 'required|exists:ai_models,id',
            'is_active' => 'boolean',
        ]);

        AiChat::create($validated);

        return redirect()->route('ai.chats.index')->with('success', 'AI Chat created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AiChat  $aiChat
     * @return \Illuminate\Http\Response
     */
    public function show(AiChat $aiChat)
    {
        $aiChat->load(['messages', 'resources']);
        return view('ai.chats.show', compact('aiChat'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AiChat  $aiChat
     * @return \Illuminate\Http\Response
     */
    public function edit(AiChat $aiChat)
    {
        return view('ai.chats.edit', [
            'aiChat' => $aiChat,
            'aiModels' => AiModel::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AiChat  $aiChat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiChat $aiChat)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'ai_model_id' => 'required|exists:ai_models,id',
            'is_active' => 'boolean',
        ]);

        $aiChat->update($validated);

        return redirect()->route('ai.chats.index')->with('success', 'AI Chat updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AiChat  $aiChat
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiChat $aiChat)
    {
        $aiChat->delete();

        return redirect()->route('ai.chats.index')->with('success', 'AI Chat deleted successfully');
    }
}
