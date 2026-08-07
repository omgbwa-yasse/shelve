'use client';

import { useQuery, useQueryClient } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import type { Entity } from '@/lib/api/types';
import { useAiAssistant } from '@/features/ai-assistant/context';
import * as api from '@/features/ai-assistant/services/assistant.service';

/** Historique des conversations passées — cliquer en rouvre une dans l'onglet Chat. */
export function HistoriqueTab() {
  const queryClient = useQueryClient();
  const { activeConversationId, openConversation } = useAiAssistant();
  const { data, isLoading } = useQuery({
    queryKey: ['ai-conversations'],
    queryFn: () => api.getConversations(),
  });

  const conversations = (data?.data as Entity[] | undefined) ?? [];

  /**
   * L'historique de chat n'est jamais supprimé (voir `AiConversationController::destroy()`,
   * qui archive côté serveur) — la conversation disparaît simplement de cette liste.
   */
  async function archive(id: string, e: React.MouseEvent) {
    e.stopPropagation();
    if (!window.confirm('Archiver cette conversation ? Elle disparaîtra de cette liste mais restera conservée.')) return;
    await api.archiveConversation(id);
    if (activeConversationId === id) openConversation(null);
    queryClient.invalidateQueries({ queryKey: ['ai-conversations'] });
  }

  if (isLoading) {
    return <p className="p-3 text-sm text-muted-foreground">Chargement…</p>;
  }

  if (conversations.length === 0) {
    return <p className="p-3 text-sm text-muted-foreground">Aucune conversation pour le moment.</p>;
  }

  return (
    <ul className="divide-y divide-border overflow-y-auto">
      {conversations.map((c) => (
        <li key={String(c.id)}>
          <div
            className={`flex w-full items-center gap-2 px-3 py-2.5 text-sm hover:bg-muted ${
              activeConversationId === String(c.id) ? 'bg-primary/10' : ''
            }`}
          >
            <button
              type="button"
              onClick={() => openConversation(String(c.id))}
              className="flex flex-1 items-center gap-2 truncate text-left"
            >
              <span className="flex-1 truncate">{String(c.title ?? 'Conversation')}</span>
              <span className="shrink-0 text-xs text-muted-foreground">{Number(c.messages_count ?? 0)} msg</span>
            </button>
            <button
              type="button"
              onClick={(e) => void archive(String(c.id), e)}
              className="shrink-0 rounded p-1 text-muted-foreground hover:bg-muted-foreground/10"
              aria-label="Archiver"
              title="Archiver (jamais supprimée)"
            >
              <Icon name="archive" className="h-3.5 w-3.5" />
            </button>
          </div>
        </li>
      ))}
    </ul>
  );
}
