'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { useQuery } from '@tanstack/react-query';
import clsx from 'clsx';
import { Icon, type IconKey } from '@/components/icons';
import { findActiveDomain } from '@/lib/navigation';
import { apiFetch } from '@/lib/api/client';
import { getMailExternalCapabilities } from '@/features/mails/services/mail.service';
import { useModal } from '@/context/ModalProvider';
import { UniversalModal } from '@/components/ui/UniversalModal';
import { recordTypesApi } from '@/features/records/services/record.service';
import type { Entity } from '@/lib/api/types';
import type { NavItem } from '@/types';

/**
 * Domaines dont les groupes sont repliés par défaut (sauf le premier) pour
 * gagner de la place : Outils et Paramètres. Les autres domaines gardent
 * leurs groupes ouverts par défaut.
 */
const COLLAPSED_BY_DEFAULT: Record<string, boolean> = {
  tools: true,
  settings: true,
};

/**
 * Second bandeau, à droite du rail : sections + boutons du domaine actif.
 * Chaque groupe est repliable (clic sur son en-tête). Pour les domaines
 * `COLLAPSED_BY_DEFAULT`, un bouton « Tout afficher / Tout masquer » pilote
 * l'ensemble et les groupes sont fermés par défaut sauf le premier.
 */
export function Submenu() {
  const pathname = usePathname();
  const router = useRouter();
  const modal = useModal();
  const activeDomain = findActiveDomain(pathname);

  // Section "Email" du domaine Mails : masquée tant qu'un administrateur ne
  // l'a pas activée pour l'organisation courante (voir submenu/mails.blade.php,
  // même condition côté Blade — `organisations.email_module_enabled`).
  const { data: emailModuleData } = useQuery({
    queryKey: ['email-status'],
    queryFn: () => apiFetch<{ data: { enabled: boolean } }>('/api/v1/email'),
    staleTime: 60_000,
    enabled: activeDomain?.key === 'mails',
  });
  const emailModuleEnabled = emailModuleData?.data.enabled ?? false;

  // Boutons courrier externe (consultation "Envoyer/Recevoir (externe)" ET
  // création "Sortant/Entrant (externe)") : masqués selon les capacités de
  // l'organisation courante (`mail_external_send_enabled` / `mail_external_receive_enabled`).
  const { data: mailCapsData } = useQuery({
    queryKey: ['mail-external-capabilities'],
    queryFn: getMailExternalCapabilities,
    staleTime: 60_000,
    enabled: activeDomain?.key === 'mails',
  });
  const externalSendEnabled = mailCapsData?.data.external_send_enabled ?? false;
  const externalReceiveEnabled = mailCapsData?.data.external_receive_enabled ?? false;

  const visibleItems = (activeDomain?.items ?? []).filter(
    (item) =>
      (item.group !== 'Email' || emailModuleEnabled) &&
      ((item.key !== 'mails-ext-send' && item.key !== 'mails-create-ext-out') || externalSendEnabled) &&
      ((item.key !== 'mails-ext-receive' && item.key !== 'mails-create-ext-in') || externalReceiveEnabled),
  );
  const groups = groupByHeading(visibleItems);

  // `allCollapsed` : null = état par défaut du domaine ; true/false = forcé.
  const [allCollapsed, setAllCollapsed] = useState<boolean | null>(null);
  const [manual, setManual] = useState<Record<string, boolean>>({});

  // Recalcule l'état par défaut quand on change de domaine.
  useEffect(() => {
    setAllCollapsed(null);
    setManual({});
  }, [activeDomain?.key]);

  const collapsible = !!activeDomain && COLLAPSED_BY_DEFAULT[activeDomain.key] === true;

  function isCollapsed(group: string, index: number): boolean {
    if (allCollapsed !== null) return allCollapsed;
    if (group in manual) return manual[group] ?? false;
    return collapsible && index > 0;
  }

  function toggleGroup(group: string, index: number) {
    setManual((prev) => ({ ...prev, [group]: !isCollapsed(group, index) }));
  }

  function toggleAll() {
    setAllCollapsed(allCollapsed !== true);
  }

  const effectivelyAllCollapsed = allCollapsed === true || (allCollapsed === null && collapsible && groups.every((_, i) => i > 0));

  /**
   * Items à action (ex. `pick-record-type`) : ouvre la modale de choix de
   * typologie SUR la vue courante — aucune navigation ni vue en arrière-plan.
   * La navigation vers le formulaire ne se fait qu'après la sélection du type.
   */
  function handleItemAction(item: NavItem) {
    const kind = new URL(item.href, 'http://localhost').searchParams.get('kind');
    if (item.action === 'pick-record-type' && (kind === 'folder' || kind === 'document')) {
      modal.open((close) => (
        <UniversalModal<Entity>
          open
          onClose={close}
          title={kind === 'folder' ? 'Choisir une typologie de dossier' : 'Choisir une typologie de document'}
          queryKey={['record-types-picker', kind]}
          queryFn={() => recordTypesApi.list({ 'page.size': 200, 'filter[is_container][eq]': kind === 'folder' ? '1' : '0' } as never)}
          rowKey={(t) => String(t.id)}
          cardTitleField="name"
          cardSubtitleField="description"
          searchMinChars={3}
          alphabet
          pageSize={24}
          emptyLabel={`Aucune typologie ${kind === 'folder' ? 'de dossier' : 'de document'} configurée.`}
          onSelect={(sel) => {
            close();
            const typeId = sel[0]?.id;
            if (typeId) router.push(`/records/create?kind=${kind}&type_id=${typeId}`);
          }}
        />
      ));
    }
  }

  // Domaines sans sous-menu (ex. Notices : écran à onglets) → pas de bandeau.
  // Placé après tous les hooks (règle des hooks de React).
  if (visibleItems.length === 0) {
    return null;
  }

  return (
    <aside
      aria-label="Sous-menu"
      className="flex w-submenu shrink-0 flex-col gap-3 overflow-y-auto border-r border-border bg-surface p-3"
    >
      <div className="flex items-center justify-between px-2">
        <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
          {activeDomain?.label ?? 'Navigation'}
        </p>
        {collapsible && groups.length > 1 && (
          <button
            type="button"
            onClick={toggleAll}
            title={effectivelyAllCollapsed ? 'Tout afficher' : 'Tout masquer'}
            className="flex items-center gap-1 rounded px-1.5 py-0.5 text-[11px] font-medium text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            <Icon name={effectivelyAllCollapsed ? 'chevronDown' : 'chevronUp'} className="h-3.5 w-3.5" />
            {effectivelyAllCollapsed ? 'Tout afficher' : 'Tout masquer'}
          </button>
        )}
      </div>

      {groups.map(([group, items], index) => {
        const collapsed = isCollapsed(group, index);

        return (
          <div key={group}>
            {group ? (
              <button
                type="button"
                onClick={() => toggleGroup(group, index)}
                className="flex w-full items-center justify-between rounded px-2 py-1 text-left text-[11px] font-semibold uppercase tracking-wide text-muted-foreground/70 hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                title={collapsed ? 'Afficher' : 'Masquer'}
              >
                <span>{group}</span>
                <Icon name={collapsed ? 'chevronRight' : 'chevronDown'} className="h-3 w-3" />
              </button>
            ) : (
              <p className="mb-1 px-2 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground/70">{group}</p>
            )}

            {!collapsed && (
              <div className="mt-0.5 flex flex-col gap-0.5">
                {items.map((item) => {
                  const active = pathname === item.href;

                  const cls = clsx(
                    'flex items-center gap-2 rounded px-2 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary',
                    active ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted',
                  );

                  if (item.action) {
                    return (
                      <button
                        key={item.key}
                        type="button"
                        onClick={() => handleItemAction(item)}
                        className={cls + ' w-full text-left'}
                      >
                        <Icon name={item.icon as IconKey} className="h-4 w-4 shrink-0" />
                        <span className="truncate">{item.label}</span>
                      </button>
                    );
                  }

                  return (
                    <Link
                      key={item.key}
                      href={item.href as never}
                      className={cls}
                    >
                      <Icon name={item.icon as IconKey} className="h-4 w-4 shrink-0" />
                      <span className="truncate">{item.label}</span>
                    </Link>
                  );
                })}
              </div>
            )}
          </div>
        );
      })}
    </aside>
  );
}

/** Regroupe les items consécutifs par `group`, en préservant l'ordre d'origine. */
function groupByHeading(items: NavItem[]): [string, NavItem[]][] {
  const groups: [string, NavItem[]][] = [];

  for (const item of items) {
    const label = item.group ?? '';
    const last = groups[groups.length - 1];

    if (last && last[0] === label) {
      last[1].push(item);
    } else {
      groups.push([label, [item]]);
    }
  }

  return groups;
}
