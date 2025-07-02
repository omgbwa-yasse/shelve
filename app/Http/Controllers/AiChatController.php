<?php

namespace App\Http\Controllers;

use App\Models\AiChat;
use App\Models\AiModel;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AiChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $chats = AiChat::with(['user', 'aiModel'])->paginate(15);
        return view('ai.chats.index', compact('chats'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('ai.chats.create', [
            'aiModels' => AiModel::all(),
        ]);
    }    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'ai_model_id' => 'required|exists:ai_models,id',
            'is_active' => 'boolean',
        ]);

        // Ajouter l'utilisateur actuellement connecté
        $validated['user_id'] = $request->user()->id;

        $chat = AiChat::create($validated);

        // Rediriger vers la page de détails du chat
        return redirect()->route('ai.chats.show', ['chat' => $chat->id])->with('success', 'Chat AI créé avec succès');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        // Récupérer le chat avec l'ID spécifié
        $aiChat = AiChat::findOrFail($id);

        // Charger toutes les relations nécessaires pour éviter les erreurs de propriété null
        $aiChat->load(['user', 'aiModel', 'messages', 'resources']);

        return view('ai.chats.show', compact('aiChat'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $aiChat = AiChat::findOrFail($id);

        return view('ai.chats.edit', [
            'aiChat' => $aiChat,
            'aiModels' => AiModel::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $aiChat = AiChat::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'ai_model_id' => 'required|exists:ai_models,id',
            'is_active' => 'boolean',
        ]);

        // On ne modifie pas l'utilisateur associé au chat
        $aiChat->update($validated);

        return redirect()->route('ai.chats.index')->with('success', 'AI Chat updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $aiChat = AiChat::findOrFail($id);
        $aiChat->delete();

        return redirect()->route('ai.chats.index')->with('success', 'AI Chat deleted successfully');
    }
}
