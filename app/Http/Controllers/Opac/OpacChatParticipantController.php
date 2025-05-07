<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\PublicChatParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpacChatParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $participants = PublicChatParticipant::with(['user', 'chat'])
            ->where('user_id', Auth::id())
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Participants retrieved successfully',
            'data' => $participants
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('opac.chat-participants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:public_chats,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $participant = PublicChatParticipant::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Participant added successfully',
            'data' => $participant
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicChatParticipant $participant)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Participant details retrieved successfully',
            'data' => $participant->load(['user', 'chat'])
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PublicChatParticipant $participant)
    {
        return view('opac.chat-participants.edit', compact('participant'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PublicChatParticipant $participant)
    {
        $validated = $request->validate([
            'chat_id' => 'sometimes|exists:public_chats,id',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        $participant->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Participant updated successfully',
            'data' => $participant
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PublicChatParticipant $participant)
    {
        $participant->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Participant removed successfully'
        ], 200);
    }
}
