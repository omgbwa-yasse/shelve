'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import clsx from 'clsx';
import { Icon } from '@/components/icons';

/**
 * Lien vers le domaine "Public" (portail OPAC + son administration) —
 * déplacé de la rail principale vers la barre organisation/langue, à droite
 * du sélecteur de langue (voir Topbar). Le domaine reste déclaré dans
 * `lib/navigation.ts` : son sous-menu continue de s'afficher normalement une
 * fois sur `/public/*` (voir Sidebar, `RAIL_HIDDEN_DOMAINS`).
 */
export function PublicLink() {
  const pathname = usePathname();
  const active = pathname === '/public' || pathname?.startsWith('/public/');

  return (
    <Link
      href="/public"
      className={clsx(
        'flex items-center gap-2 rounded border border-border px-3 py-1.5 text-sm hover:bg-muted',
        active && 'border-primary text-primary',
      )}
    >
      <Icon name="public" className="h-4 w-4" />
      <span>Public</span>
    </Link>
  );
}
