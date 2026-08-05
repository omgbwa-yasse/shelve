'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import clsx from 'clsx';
import { Icon, type IconKey } from '@/components/icons';
import { navigation } from '@/lib/navigation';

/**
 * Bande de navigation principale (rail), fixe à gauche : une icône + un
 * libellé court par domaine, toujours visible, jamais repliée en icônes
 * seules (voir PHILOSOPHY.md, "Deux niveaux de navigation à gauche").
 */
export function Sidebar() {
  const pathname = usePathname();

  return (
    <nav
      aria-label="Domaines"
      className="flex w-rail shrink-0 flex-col items-stretch gap-1 overflow-y-auto bg-rail py-3"
    >
      {navigation.map((domain) => {
        const active = pathname?.startsWith(domain.href);

        return (
          <Link
            key={domain.key}
            href={domain.href as never}
            className={clsx(
              'flex flex-col items-center gap-1 rounded px-2 py-3 text-[11px] font-medium text-rail-foreground/80 transition-colors mx-2',
              active ? 'bg-rail-active/15 text-rail-active' : 'hover:bg-rail-foreground/10',
            )}
          >
            <Icon name={domain.icon as IconKey} className="h-5 w-5" />
            <span className="text-center leading-tight">{domain.label}</span>
          </Link>
        );
      })}
    </nav>
  );
}
