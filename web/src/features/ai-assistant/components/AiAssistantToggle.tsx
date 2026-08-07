'use client';

import clsx from 'clsx';
import { Icon } from '@/components/icons';
import { useAiAssistant } from '@/features/ai-assistant/context';

/** Icône IA de la Topbar, à côté de "Public" — ouvre/ferme le panneau assistant. */
export function AiAssistantToggle() {
  const { isOpen, toggle } = useAiAssistant();

  return (
    <button
      type="button"
      onClick={toggle}
      aria-pressed={isOpen}
      aria-label="Assistant IA"
      className={clsx(
        'flex items-center gap-2 rounded border border-border px-3 py-1.5 text-sm hover:bg-muted',
        isOpen && 'border-primary text-primary',
      )}
    >
      <Icon name="sparkles" className="h-4 w-4" />
      <span>IA</span>
    </button>
  );
}
