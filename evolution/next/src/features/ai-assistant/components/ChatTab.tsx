'use client';

import { useEffect, useState } from 'react';
import { usePathname, useSearchParams } from 'next/navigation';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import clsx from 'clsx';
import { Icon } from '@/components/icons';
import type { Entity } from '@/lib/api/types';
import { useAiAssistant } from '@/features/ai-assistant/context';
import * as api from '@/features/ai-assistant/services/assistant.service';
import type { AssistantMode } from '@/features/ai-assistant/services/assistant.service';

const MODES: AssistantMode[] = ['manuel', 'edit', 'plan', 'autonome'];

/**
 * Discussion avec l'assistant IA — transmet le contexte de la page active
 * (chemin + querystring) à chaque message, voir `AiAssistantChatService`
 * côté Laravel (`systemPrompt()` l'injecte dans le prompt système). Le mode
 * choisi (voir `AssistantMode`) pilote le degré de confirmation exigé avant
 * une action de création/modification/suppression.
 */
export function ChatTab() {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const { activeConversationId, openConversation } = useAiAssistant();
  const queryClient = useQueryClient();
  const [draft, setDraft] = useState('');
  const [sending, setSending] = useState(false);
  const [mode, setMode] = useState<AssistantMode>('manuel');

  const pageContext: api.PageContext = {
    path: pathname ?? '/',
    search: searchParams?.toString() || undefined,
  };

  const { data } = useQuery({
    queryKey: ['ai-conversation', activeConversationId],
    queryFn: () => api.getConversation(activeConversationId as string),
    enabled: activeConversationId !== null,
  });

  const messages = (data?.data.messages as Entity[] | undefined) ?? [];
  const conversationMode = data?.data.mode as AssistantMode | undefined;

  // Reflète le mode de la conversation rouverte (Historique) ; retombe sur
  // "manuel" pour un nouveau fil.
  useEffect(() => {
    setMode(conversationMode ?? 'manuel');
  }, [activeConversationId, conversationMode]);

  async function send() {
    const message = draft.trim();
    if (!message || sending) return;

    setSending(true);
    setDraft('');
    try {
      if (activeConversationId === null) {
        const response = await api.startConversation(message, pageContext, mode);
        openConversation(String(response.data.id));
        queryClient.invalidateQueries({ queryKey: ['ai-conversations'] });
      } else {
        await api.sendConversationMessage(activeConversationId, message, pageContext, mode);
        queryClient.invalidateQueries({ queryKey: ['ai-conversation', activeConversationId] });
        queryClient.invalidateQueries({ queryKey: ['ai-conversations'] });
      }
    } finally {
      setSending(false);
    }
  }

  return (
    <div className="flex h-full flex-col">
      <div className="flex gap-1 border-b border-border p-2" role="radiogroup" aria-label="Mode de l'assistant">
        {MODES.map((m) => (
          <button
            key={m}
            type="button"
            role="radio"
            aria-checked={mode === m}
            title={api.ASSISTANT_MODE_DESCRIPTIONS[m]}
            onClick={() => setMode(m)}
            className={clsx(
              'flex-1 rounded px-2 py-1 text-xs font-medium transition-colors',
              mode === m ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground hover:text-foreground',
            )}
          >
            {api.ASSISTANT_MODE_LABELS[m]}
          </button>
        ))}
      </div>
      <p className="border-b border-border px-3 py-1.5 text-xs text-muted-foreground">
        {api.ASSISTANT_MODE_DESCRIPTIONS[mode]}
      </p>

      <div className="flex-1 space-y-3 overflow-y-auto p-3">
        {messages.length === 0 ? (
          <p className="text-sm text-muted-foreground">
            Posez une question sur la page en cours ({pageContext.path}) ou sur vos archives.
          </p>
        ) : (
          messages.map((m) => (
            <div
              key={String(m.id)}
              className={m.role === 'user' ? 'ml-auto max-w-[85%] rounded-lg bg-primary/10 px-3 py-2 text-sm' : 'mr-auto max-w-[85%] rounded-lg bg-muted px-3 py-2 text-sm'}
            >
              {String(m.content)}
            </div>
          ))
        )}
        {sending && <div className="mr-auto max-w-[85%] rounded-lg bg-muted px-3 py-2 text-sm text-muted-foreground">…</div>}
      </div>

      <form
        onSubmit={(e) => {
          e.preventDefault();
          void send();
        }}
        className="flex items-end gap-2 border-t border-border p-3"
      >
        <textarea
          value={draft}
          onChange={(e) => setDraft(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
              e.preventDefault();
              void send();
            }
          }}
          placeholder="Écrire un message…"
          rows={2}
          className="flex-1 resize-none rounded border border-border bg-background px-2 py-1.5 text-sm outline-none focus:border-primary"
        />
        <button
          type="submit"
          disabled={sending || draft.trim() === ''}
          className="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-primary text-primary-foreground disabled:opacity-50"
        >
          <Icon name="send" className="h-4 w-4" />
        </button>
      </form>
    </div>
  );
}
