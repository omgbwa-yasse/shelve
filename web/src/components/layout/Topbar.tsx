'use client';

import { Icon } from '@/components/icons';
import { OrganisationSwitcher } from '@/components/layout/OrganisationSwitcher';
import { LanguageSwitcher } from '@/components/layout/LanguageSwitcher';
import { PublicLink } from '@/components/layout/PublicLink';
import { AiAssistantToggle } from '@/features/ai-assistant/components/AiAssistantToggle';
import type { Organisation } from '@/types';

/**
 * Bandeau supérieur : recherche globale à gauche, organisation + langue à
 * droite — toujours visibles, jamais dans un menu replié (voir
 * PHILOSOPHY.md, "Ce qui doit rester visible en permanence").
 */
export function Topbar({ organisations = [] }: { organisations?: Organisation[] }) {
  return (
    <header className="flex h-topbar shrink-0 items-center gap-4 border-b border-border bg-background px-4">
      <div className="relative flex-1 max-w-xl">
        <Icon name="search" className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
        <input
          type="search"
          placeholder="Rechercher une notice, un courrier, un contact…"
          className="w-full rounded border border-border bg-surface py-2 pl-9 pr-3 text-sm outline-none focus:border-primary"
        />
      </div>

      <div className="ml-auto flex items-center gap-3">
        <OrganisationSwitcher organisations={organisations} />
        <LanguageSwitcher />
        <PublicLink />
        <AiAssistantToggle />
      </div>
    </header>
  );
}
