<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workplace;
use App\Models\WorkplaceConversation;
use App\Models\WorkplaceConversationParticipant;
use App\Models\WorkplaceMessage;
use Illuminate\Http\Request;

class WorkplaceMessageController extends Controller
{
    public function index(Workplace $workplace)
    {
        $this->authorize('view', $workplace);

        return $this->renderChat($workplace);
    }

    public function show(Workplace $workplace, WorkplaceConversation $conversation)
    {
        $this->authorize('view', $workplace);

        if ($conversation->workplace_id !== $workplace->id) {
            abort(404);
        }

        if (!$conversation->isParticipant(auth()->id())) {
            abort(403);
        }

        $conversation->participants()->where('user_id', auth()->id())->update(['last_read_at' => now()]);
        $activeConversation = $conversation->load(['creator', 'participants.user', 'messages.user']);

        return $this->renderChat($workplace, $activeConversation);
    }

    public function store(Request $request, Workplace $workplace)
    {
        $this->authorize('view', $workplace);

        $request->validate([
            'type' => 'required|in:group,channel,private',
            'name' => 'required_if:type,group,channel|max:150',
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:users,id',
        ]);

        // Conversation privée : réutiliser une existante entre les deux membres
        if ($request->type === 'private') {
            $otherId = (int) $request->participant_ids[0];
            $existing = WorkplaceConversation::where('workplace_id', $workplace->id)
                ->where('type', 'private')
                ->whereHas('participants', fn ($q) => $q->where('user_id', auth()->id()))
                ->whereHas('participants', fn ($q) => $q->where('user_id', $otherId))
                ->get()
                ->first(fn ($c) => $c->participants()->count() === 2);

            if ($existing) {
                return redirect()->route('workplaces.messages.show', [$workplace, $existing]);
            }
        }

        $conversation = WorkplaceConversation::create([
            'workplace_id' => $workplace->id,
            'type' => $request->type,
            'name' => $request->type === 'private' ? null : $request->name,
            'created_by' => auth()->id(),
        ]);

        $participantIds = array_values(array_unique(array_merge([auth()->id()], $request->participant_ids)));

        foreach ($participantIds as $userId) {
            WorkplaceConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => (int) $userId,
                'role' => (int) $userId === auth()->id() ? 'owner' : 'member',
            ]);
        }

        return redirect()->route('workplaces.messages.show', [$workplace, $conversation])
            ->with('success', 'Conversation créée.');
    }

    public function storeMessage(Request $request, Workplace $workplace, WorkplaceConversation $conversation)
    {
        $this->authorize('view', $workplace);

        if ($conversation->workplace_id !== $workplace->id) {
            abort(404);
        }

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

        return redirect()->route('workplaces.messages.show', [$workplace, $conversation]);
    }

    public function destroy(Workplace $workplace, WorkplaceConversation $conversation)
    {
        $this->authorize('view', $workplace);

        if ($conversation->workplace_id !== $workplace->id) {
            abort(404);
        }

        if (auth()->id() !== $conversation->created_by) {
            abort(403);
        }

        $conversation->delete();

        return redirect()->route('workplaces.messages.index', $workplace)
            ->with('success', 'Conversation supprimée.');
    }

    private function renderChat(Workplace $workplace, ?WorkplaceConversation $activeConversation = null)
    {
        $conversations = WorkplaceConversation::where('workplace_id', $workplace->id)
            ->whereHas('participants', fn ($q) => $q->where('user_id', auth()->id()))
            ->with(['creator', 'participants.user', 'messages' => fn ($q) => $q->select('id', 'conversation_id', 'user_id', 'content', 'created_at')])
            ->orderBy('updated_at', 'desc')
            ->get();

        $members = User::whereIn('id', $workplace->members()->pluck('user_id'))->orderBy('name')->get();

        $groups = $conversations->where('type', 'group')->values();
        $channels = $conversations->where('type', 'channel')->values();
        $privates = $conversations->where('type', 'private')->values();

        return view('workplaces.messages.index', compact(
            'workplace',
            'conversations',
            'groups',
            'channels',
            'privates',
            'members',
            'activeConversation'
        ));
    }
}
