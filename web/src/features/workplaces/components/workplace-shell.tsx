'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import clsx from 'clsx';
import { useWorkplace } from '@/features/workplaces/context';
import { Icon } from '@/components/icons';

type TabKey = 'dashboard' | 'messages' | 'members' | 'activities' | 'calendar' | 'documents';

const TABS: { key: TabKey; label: string; icon: 'dashboard' | 'messageCircle' | 'users' | 'history' | 'calendar' | 'folderOpen' }[] = [
  { key: 'dashboard', label: 'Tableau de bord', icon: 'dashboard' },
  { key: 'documents', label: 'Documents', icon: 'folderOpen' },
  { key: 'messages', label: 'Messages', icon: 'messageCircle' },
  { key: 'members', label: 'Membres', icon: 'users' },
  { key: 'activities', label: 'Activités', icon: 'history' },
  { key: 'calendar', label: 'Calendrier', icon: 'calendar' },
];

/**
 * Coquille d'un workplace — reproduit `workplaces/partials/site-header.blade.php`
 * : bannière dégradée (couleur/icône du site) + onglets de navigation internes.
 * Chaque page du layout `/workplace/{code}` est rendue sous cette coquille.
 */
export function WorkplaceShell({ children }: { children: React.ReactNode }) {
  const pathname = usePathname();
  const { code, workplace, isLoading, isError } = useWorkplace();

  if (isLoading) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <p className="text-sm text-muted-foreground">Chargement de l'espace…</p>
      </div>
    );
  }

  if (isError || !workplace) {
    return (
      <div className="flex min-h-screen flex-col items-center justify-center gap-3">
        <Icon name="briefcase" className="h-10 w-10 text-muted-foreground/40" />
        <p className="text-sm text-muted-foreground">Cet espace de travail est introuvable.</p>
        <Link href="/workplaces" className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted">
          Retour aux workplaces
        </Link>
      </div>
    );
  }

  const color = workplace.color ?? '#2c3e6b';
  const wPath = `/workplace/${encodeURIComponent(code)}`;

  const activeTab: TabKey = ((): TabKey => {
    if (pathname === wPath) return 'dashboard';
    if (pathname?.startsWith(`${wPath}/members`)) return 'members';
    if (pathname?.startsWith(`${wPath}/activities`)) return 'activities';
    if (pathname?.startsWith(`${wPath}/messages`)) return 'messages';
    if (pathname?.startsWith(`${wPath}/calendar`)) return 'calendar';
    if (pathname?.startsWith(`${wPath}/documents`)) return 'documents';
    return 'dashboard';
  })();

  return (
    <div className="flex min-h-screen flex-col gap-4 p-4">
      {/* ===================== BANNIÈRE ===================== */}
      <div
        className="relative flex flex-wrap items-center justify-between gap-3 overflow-hidden rounded-t-lg px-6 py-5 text-white"
        style={{ background: `linear-gradient(135deg, ${color} 0%, ${color}cc 100%)` }}
      >
        <div className="flex items-center gap-3">
          <div className="flex h-14 w-14 items-center justify-center rounded-xl bg-white/15 text-2xl">
            <Icon name="briefcase" className="h-7 w-7" />
          </div>
          <div>
            <h2 className="text-xl font-bold leading-tight">{workplace.name}</h2>
            <div className="mt-1 flex flex-wrap items-center gap-3 text-sm text-white/85">
              {workplace.category?.name && (
                <span className="flex items-center gap-1">
                  <Icon name="tags" className="h-3.5 w-3.5" />
                  {workplace.category.name}
                </span>
              )}
              <span className="flex items-center gap-1 font-mono">
                <Icon name="hash" className="h-3.5 w-3.5" />
                {workplace.code}
              </span>
              <span className="flex items-center gap-1 rounded-full bg-black/20 px-2 py-0.5 text-xs">
                <Icon name={workplace.is_public ? 'globe' : 'shieldLock'} className="h-3 w-3" />
                {workplace.is_public ? 'Public' : 'Privé'}
              </span>
              {workplace.status === 'archived' && (
                <span className="flex items-center gap-1 rounded-full bg-black/25 px-2 py-0.5 text-xs">
                  <Icon name="archive" className="h-3 w-3" />
                  Archivé
                </span>
              )}
            </div>
          </div>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <Link
            href="/workplaces"
            title="Retour à la liste des workplaces"
            className="rounded border border-white/30 bg-white/15 px-3 py-1.5 text-sm hover:bg-white/25"
          >
            <Icon name="arrowRightSquare" className="mr-1 inline h-4 w-4 rotate-180" />
            Retour
          </Link>
          <Link href={`${wPath}/edit`} className="rounded border border-white/30 bg-white/15 px-3 py-1.5 text-sm hover:bg-white/25">
            <Icon name="save" className="mr-1 inline h-4 w-4" />
            Modifier
          </Link>
          <Link href={`${wPath}/settings`} className="rounded border border-white/30 bg-white/15 px-3 py-1.5 text-sm hover:bg-white/25">
            <Icon name="settings" className="mr-1 inline h-4 w-4" />
            Paramètres
          </Link>
        </div>
        <div className="pointer-events-none absolute -right-10 -top-10 h-44 w-44 rounded-full bg-white/5" />
        <div className="pointer-events-none absolute -bottom-14 right-16 h-28 w-28 rounded-full bg-white/5" />
      </div>

      {/* ===================== ONGLETS ===================== */}
      <nav className="flex gap-1 overflow-x-auto rounded-b-lg border border-t-0 border-border bg-surface px-3">
        {TABS.map((tab) => {
          const active = activeTab === tab.key;
          return (
            <Link
              key={tab.key}
              href={tab.key === 'dashboard' ? wPath : `${wPath}/${tab.key}`}
              className={clsx(
                'flex items-center gap-1.5 whitespace-nowrap border-b-2 px-3 py-2.5 text-sm font-medium transition-colors',
                active
                  ? 'border-primary text-primary'
                  : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground',
              )}
            >
              <Icon name={tab.icon} className="h-4 w-4" />
              {tab.label}
            </Link>
          );
        })}
      </nav>

      {/* ===================== CONTENU ===================== */}
      <div className="pb-4">{children}</div>
    </div>
  );
}
