<?php

namespace App\Http\Controllers;

use App\Models\PublicChatParticipant;
use Illuminate\Http\Request;

class PublicChatParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $participants = PublicChatParticipant::with(['user', 'chat'])->paginate(10);
        return view('public.chat-participants.index', compact('participants'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('public.chat-participants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:public_chats,id',
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,moderator,member',
        ]);

        PublicChatParticipant::create($validated);

        return redirect()->route('public.chat-participants.index')
            ->with('success', 'Chat participant added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicChatParticipant $chatParticipant)
    {
        return view('public.chat-participants.show', compact('chatParticipant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicChatParticipant $chatParticipant)
    {
        return view('public.chat-participants.edit', compact('chatParticipant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicChatParticipant $chatParticipant)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,moderator,member',
        ]);

        $chatParticipant->update($validated);

        return redirect()->route('public.chat-participants.index')
            ->with('success', 'Chat participant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicChatParticipant $chatParticipant)
    {
        $chatParticipant->delete();

        return redirect()->route('public.chat-participants.index')
            ->with('success', 'Chat participant removed successfully.');
    }
}
