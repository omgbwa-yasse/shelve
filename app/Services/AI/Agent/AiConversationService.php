<?php

namespace App\Services\AI\Agent;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Persiste l'historique des conversations avec l'assistant IA archiviste,
 * pour que l'utilisateur puisse retrouver ses échanges passés au-delà de
 * l'état volatile du navigateur (rechargement de page, autre poste...).
 */
class AiConversationService
{
    /**
     * Ajoute un tour (question utilisateur + réponse) à une conversation.
     * Crée la conversation si $conversationId est null ou introuvable pour
     * l'utilisateur (jamais de fuite entre comptes).
     */
    public function record(User $user, ?int $conversationId, string $question, string $answer): ChatConversation
    {
        $conversation = $conversationId
            ? ChatConversation::query()->where('user_id', $user->id)->find($conversationId)
            : null;

        if (!$conversation) {
            $conversation = ChatConversation::create([
                'user_id' => $user->id,
                'title' => Str::limit($question, 80),
                'status' => 'active',
            ]);
        }

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $question,
        ]);

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $answer,
        ]);

        $conversation->touch();

        return $conversation;
    }

    /**
     * Liste les conversations récentes de l'utilisateur (les plus actives en premier).
     */
    public function recentConversations(User $user, int $limit = 20)
    {
        return ChatConversation::query()
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->withCount('messages')
            ->get();
    }

    /**
     * Détail d'une conversation avec ses messages, scopé à l'utilisateur.
     */
    public function conversationWithMessages(User $user, int $conversationId): ?ChatConversation
    {
        return ChatConversation::query()
            ->where('user_id', $user->id)
            ->with(['messages' => fn ($q) => $q->orderBy('created_at')])
            ->find($conversationId);
    }
}
