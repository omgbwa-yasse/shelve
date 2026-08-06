<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Conversation entre un agent et l'assistant IA du panneau latéral.
 *
 * Mode (voir demande utilisateur du 2026-08-05) :
 * - MODE_MANUEL (défaut) : confirmation systématique avant toute action de
 *   création, modification ou suppression.
 * - MODE_EDIT : les modifications ("edits") peuvent être présentées comme
 *   pré-approuvées ; création et suppression restent toujours confirmées.
 * - MODE_PLAN : l'assistant ne produit qu'un plan, n'exécute ni ne propose
 *   jamais d'action comme faite.
 * - MODE_AUTONOME : l'assistant agit sans confirmation répétée, mais
 *   strictement dans la limite des permissions de l'agent (voir
 *   `AiCapabilityService`) — jamais au-delà.
 *
 * Aucun de ces modes ne contourne les policies Laravel : aujourd'hui le chat
 * ne dispose d'aucune capacité d'exécution CRUD réelle (voir
 * `AiAssistantChatService`) — le mode ne pilote que le prompt système, donc
 * le ton et le format de la réponse, pas un contournement de sécurité.
 */
class AiConversation extends Model
{
    use HasFactory, BelongsToOrganisation;

    public const MODE_MANUEL = 'manuel';
    public const MODE_EDIT = 'edit';
    public const MODE_PLAN = 'plan';
    public const MODE_AUTONOME = 'autonome';

    public const MODES = [self::MODE_MANUEL, self::MODE_EDIT, self::MODE_PLAN, self::MODE_AUTONOME];

    protected $fillable = [
        'organisation_id',
        'user_id',
        'title',
        'context',
        'archived_at',
        'mode',
    ];

    protected $casts = [
        'context' => 'array',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'conversation_id')->orderBy('created_at');
    }

    /**
     * Masque la conversation de l'onglet Historique sans jamais la
     * supprimer — l'historique d'échanges avec l'assistant IA ne doit
     * jamais être effacé (exigence utilisateur du 2026-08-05).
     */
    public function archive(): void
    {
        $this->update(['archived_at' => now()]);
    }

    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }

    /** Dérive un titre à partir du premier message si aucun n'a été fourni. */
    public static function titleFromMessage(string $message): string
    {
        $title = trim(preg_replace('/\s+/', ' ', $message));

        return mb_strlen($title) > 80 ? mb_substr($title, 0, 77) . '…' : $title;
    }
}
