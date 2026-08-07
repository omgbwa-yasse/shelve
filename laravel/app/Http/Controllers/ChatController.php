<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceConversation;
use App\Models\WorkplaceConversationParticipant;
use App\Models\WorkplaceMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return $this->renderChat();
    }

    public function show(WorkplaceConversation $conversation)
    {
        if (!$conversation->isParticipant(auth()->id())) {
            abort(403);
        }

        $conversation->participants()->where('user_id', auth()->id())->update(['last_read_at' => now()]);
        $activeConversation = $conversation->load(['creator', 'participants.user', 'messages.user', 'workplace']);

        return $this->renderChat($activeConversation);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $otherId = (int) $request->user_id;

        $existing = WorkplaceConversation::whereNull('workplace_id')
            ->where('type', 'private')
            ->whereHas('participants', fn ($q) => $q->where('user_id', auth()->id()))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $otherId))
            ->get()
            ->first(fn ($c) => $c->participants()->count() === 2);

        if ($existing) {
            return redirect()->route('chats.show', $existing);
        }

        $conversation = WorkplaceConversation::create([
            'workplace_id' => null,
            'type' => 'private',
            'name' => null,
            'created_by' => auth()->id(),
        ]);

        foreach (array_unique([auth()->id(), $otherId]) as $userId) {
            WorkplaceConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => (int) $userId,
                'role' => (int) $userId === auth()->id() ? 'owner' : 'member',
            ]);
        }

        return redirect()->route('chats.show', $conversation)->with('success', 'Message privé créé.');
    }

    public function storeMessage(Request $request, WorkplaceConversation $conversation)
    {
        if (!$conversation->isParticipant(auth()->id())) {
            abort(403);
        }

        $request->validate(['content' => 'required|string']);

        WorkplaceMessage::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        $conversation->touch();

        return redirect()->route('chats.show', $conversation);
    }

    public function destroy(WorkplaceConversation $conversation)
    {
        if (auth()->id() !== $conversation->created_by) {
            abort(403);
        }

        $conversation->delete();

        return redirect()->route('chats.index')->with('success', 'Conversation supprimée.');
    }

    private function renderChat(?WorkplaceConversation $activeConversation = null)
    {
        $base = fn ($query) => $query->with([
            'creator',
            'participants.user',
            'workplace',
            'messages' => fn ($q) => $q->select('id', 'conversation_id', 'user_id', 'content', 'created_at'),
        ])->orderBy('updated_at', 'desc');

        $directs = $base(
            WorkplaceConversation::whereNull('workplace_id')
                ->where('type', 'private')
                ->whereHas('participants', fn ($q) => $q->where('user_id', auth()->id()))
        )->get();

        $workplaceConvs = $base(
            WorkplaceConversation::whereNotNull('workplace_id')
                ->whereHas('participants', fn ($q) => $q->where('user_id', auth()->id()))
        )->get();

        $colleagues = User::where('id', '!=', auth()->id())
            ->whereHas('organisations', fn ($q) => $q->whereIn('user_organisation_role.organisation_id', auth()->user()->organisations()->pluck('user_organisation_role.organisation_id')))
            ->orderBy('name')
            ->get();

        return view('chats.index', compact('directs', 'workplaceConvs', 'colleagues', 'activeConversation'));
    }
}
