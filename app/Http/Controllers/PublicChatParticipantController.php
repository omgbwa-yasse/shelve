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
        $chats = \App\Models\PublicChat::latest()->get();
        $users = \App\Models\PublicUser::latest()->get();
        return view('public.chat-participants.create', compact('chats', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:public_chats,id',
            'user_id' => 'required|exists:public_users,id',
            'role' => 'required|in:admin,moderator,member',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Check if participant already exists
        $exists = PublicChatParticipant::where('chat_id', $validated['chat_id'])
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Cet utilisateur participe déjà à cette discussion.');
        }

        PublicChatParticipant::create($validated);

        return redirect()->route('public.chat-participants.index')
            ->with('success', 'Participant ajouté avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicChatParticipant $participant)
    {
        return view('public.chat-participants.show', compact('participant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicChatParticipant $participant)
    {
        $chats = \App\Models\PublicChat::latest()->get();
        $users = \App\Models\PublicUser::latest()->get();
        return view('public.chat-participants.edit', compact('participant', 'chats', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicChatParticipant $participant)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:public_chats,id',
            'user_id' => 'required|exists:public_users,id',
            'role' => 'required|in:admin,moderator,member',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Check if participant already exists for another entry
        $exists = PublicChatParticipant::where('chat_id', $validated['chat_id'])
            ->where('user_id', $validated['user_id'])
            ->where('id', '!=', $participant->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Cet utilisateur participe déjà à cette discussion.');
        }

        $participant->update($validated);

        return redirect()->route('public.chat-participants.index')
            ->with('success', 'Participant modifié avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicChatParticipant $participant)
    {
        $participant->delete();

        return redirect()->route('public.chat-participants.index')
            ->with('success', 'Participant retiré avec succès.');
    }
}
