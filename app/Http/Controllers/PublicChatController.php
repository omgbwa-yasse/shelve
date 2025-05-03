<?php

namespace App\Http\Controllers;

use App\Models\PublicChat;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PublicChatController extends Controller
{
    /**
     * Display a listing of the chats.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $chats = PublicChat::with('participants')->get();
        return view('public-chats.index', compact('chats'));
    }

    /**
     * Show the form for creating a new chat.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = PublicUser::all();
        return view('public-chats.create', compact('users'));
    }

    /**
     * Store a newly created chat in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'is_group' => 'boolean',
            'is_active' => 'boolean',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:public_users,id',
        ]);

        // Create the chat
        $chat = PublicChat::create([
            'title' => $validated['title'],
            'is_group' => $validated['is_group'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Attach users to chat
        foreach ($validated['user_ids'] as $userId) {
            $chat->participants()->create([
                'user_id' => $userId,
                'is_admin' => $userId == $request->user_id, // Make creator an admin
                'last_read_at' => now(),
            ]);
        }

        return redirect()->route('public-chats.index')
            ->with('success', 'Chat created successfully');
    }

    /**
     * Display the specified chat.
     *
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function show(PublicChat $publicChat)
    {
        $publicChat->load(['messages.user', 'participants.user']);
        return view('public-chats.show', compact('publicChat'));
    }

    /**
     * Show the form for editing the specified chat.
     *
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function edit(PublicChat $publicChat)
    {
        $users = PublicUser::all();
        $participants = $publicChat->participants->pluck('user_id')->toArray();

        return view('public-chats.edit', compact('publicChat', 'users', 'participants'));
    }

    /**
     * Update the specified chat in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PublicChat $publicChat)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'is_group' => 'boolean',
            'is_active' => 'boolean',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:public_users,id',
        ]);

        // Update the chat
        $publicChat->update([
            'title' => $validated['title'],
            'is_group' => $validated['is_group'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Sync participants
        $existingUserIds = $publicChat->participants->pluck('user_id')->toArray();
        $newUserIds = $validated['user_ids'];

        // Remove users no longer in the chat
        foreach ($existingUserIds as $userId) {
            if (!in_array($userId, $newUserIds)) {
                $publicChat->participants()->where('user_id', $userId)->delete();
            }
        }

        // Add new users to the chat
        foreach ($newUserIds as $userId) {
            if (!in_array($userId, $existingUserIds)) {
                $publicChat->participants()->create([
                    'user_id' => $userId,
                    'is_admin' => false,
                    'last_read_at' => now(),
                ]);
            }
        }

        return redirect()->route('public-chats.index')
            ->with('success', 'Chat updated successfully');
    }

    /**
     * Remove the specified chat from storage.
     *
     * @param  \App\Models\PublicChat  $publicChat
     * @return \Illuminate\Http\Response
     */
    public function destroy(PublicChat $publicChat)
    {
        $publicChat->delete();

        return redirect()->route('public-chats.index')
            ->with('success', 'Chat deleted successfully');
    }
}
