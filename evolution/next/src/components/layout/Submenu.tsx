'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import clsx from 'clsx';
import { Icon, type IconKey } from '@/components/icons';
import { findActiveDomain } from '@/lib/navigation';
import type { NavItem } from '@/types';

/**
 * Second bandeau, à droite du rail : sections + boutons du domaine actif
 * (icône + texte), reproduisant les en-têtes repliables du sous-menu Blade
 * (`submenu-heading` / `submenu-content`). Se vide si aucun domaine ne
 * correspond à l'URL — ne disparaît jamais complètement pour ne pas faire
 * "sauter" la mise en page.
 */
export function Submenu() {
  const pathname = usePathname();
  const activeDomain = findActiveDomain(pathname);

  const groups = groupByHeading(activeDomain?.items ?? []);

  return (
    <aside
      aria-label="Sous-menu"
      className="flex w-submenu shrink-0 flex-col gap-3 overflow-y-auto border-r border-border bg-surface p-3"
    >
      <p className="px-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
        {activeDomain?.label ?? 'Navigation'}
      </p>

      {groups.map(([group, items]) => (
        <div key={group}>
          {group && (
            <p className="mb-1 px-2 text-[11px] font-semibold uppercase tracking-wide text-muted-foreground/70">
              {group}
            </p>
          )}
          <div className="flex flex-col gap-0.5">
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
        </div>
      ))}
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
