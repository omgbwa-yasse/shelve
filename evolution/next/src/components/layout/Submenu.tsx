'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import clsx from 'clsx';
import { Icon, type IconKey } from '@/components/icons';
import { findActiveDomain } from '@/lib/navigation';
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
  const activeDomain = findActiveDomain(pathname);

  const groups = groupByHeading(activeDomain?.items ?? []);

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
            className="flex items-center gap-1 rounded px-1.5 py-0.5 text-[11px] font-medium text-muted-foreground hover:bg-muted hover:text-foreground"
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
                className="flex w-full items-center justify-between rounded px-2 py-1 text-left text-[11px] font-semibold uppercase tracking-wide text-muted-foreground/70 hover:bg-muted hover:text-foreground"
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

                  return (
                    <Link
                      key={item.key}
                      href={item.href as never}
                      className={clsx(
                        'flex items-center gap-2 rounded px-2 py-1.5 text-sm font-medium transition-colors',
                        active ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted',
                      )}
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
