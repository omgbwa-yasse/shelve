'use client';

import { useState } from 'react';
import clsx from 'clsx';
import { Icon } from '@/components/icons';
import { useAiAssistant } from '@/features/ai-assistant/context';
import { ChatTab } from '@/features/ai-assistant/components/ChatTab';
import { RoutineTab } from '@/features/ai-assistant/components/RoutineTab';
import { HistoriqueTab } from '@/features/ai-assistant/components/HistoriqueTab';

type Tab = 'chat' | 'routine' | 'historique';

const TABS: { key: Tab; label: string }[] = [
  { key: 'chat', label: 'Chat' },
  { key: 'routine', label: 'Routine' },
  { key: 'historique', label: 'Historique' },
];

/**
 * Panneau latéral de l'assistant IA, à droite du contenu (voir
 * `(back-office)/layout.tsx` : sibling de `<main>` dans la même rangée flex,
 * pas une surcouche par-dessus le rail/topbar). Ouvert/fermé depuis l'icône
 * IA de la Topbar (voir `context.tsx`).
 */
export function AiAssistantPanel() {
  const { isOpen, close } = useAiAssistant();
  const [tab, setTab] = useState<Tab>('chat');

  if (!isOpen) return null;

  return (
    <aside
      aria-label="Assistant IA"
      className="flex w-96 shrink-0 flex-col border-l border-border bg-surface"
    >
      <header className="flex items-center justify-between border-b border-border px-3 py-2.5">
        <div className="flex items-center gap-2 text-sm font-semibold">
          <Icon name="sparkles" className="h-4 w-4 text-primary" />
          Assistant IA
        </div>
        <button type="button" onClick={close} className="rounded p-1 hover:bg-muted" aria-label="Fermer">
          <Icon name="close" className="h-4 w-4" />
        </button>
      </header>

      <nav className="flex border-b border-border text-sm">
        {TABS.map((t) => (
          <button
            key={t.key}
            type="button"
            onClick={() => setTab(t.key)}
            className={clsx(
              'flex-1 border-b-2 px-3 py-2 font-medium transition-colors',
              tab === t.key ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground',
            )}
          >
            {t.label}
          </button>
        ))}
      </nav>

      <div className="min-h-0 flex-1">
        {tab === 'chat' && <ChatTab />}
        {tab === 'routine' && <RoutineTab />}
        {tab === 'historique' && <HistoriqueTab />}
      </div>
    </aside>
  );
}
