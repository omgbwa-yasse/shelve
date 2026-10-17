'use client';

import { Suspense, useMemo, useState } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { useQuery } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import { listPublicEvents, type PublicEventFilter, type PublicEventsQuery } from '@/features/opac/services/opac.service';
import { EmptyState } from '@/features/opac/components/opac-views';

const FILTERS: { value: PublicEventFilter; label: string }[] = [
  { value: 'upcoming', label: 'À venir' },
  { value: 'this_week', label: 'Cette semaine' },
  { value: 'this_month', label: 'Ce mois' },
  { value: 'past', label: 'Passés' },
];

function formatDateLabel(value: string): string {
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatTime(value: string): string {
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function EventListInner() {
  const router = useRouter();
  const sp = useSearchParams();
  const search = sp.get('search') ?? '';
  const filter = (sp.get('filter') as PublicEventFilter) || 'upcoming';
  const page = Number(sp.get('page') ?? '1') || 1;

  const [q, setQ] = useState(search);

  const params = useMemo<PublicEventsQuery>(
    () => ({ per_page: 12, page, filter, ...(search ? { search } : {}) }),
    [search, filter, page],
  );

  const { data, isLoading, isError } = useQuery({
    queryKey: ['opac-events', params],
    queryFn: () => listPublicEvents(params),
  });

  const items = data?.data ?? [];
  const meta = data?.meta;

  function navigate(next: Record<string, string>) {
    const sp2 = new URLSearchParams(sp.toString());
    for (const [k, v] of Object.entries(next)) {
      if (v) sp2.set(k, v);
      else sp2.delete(k);
    }
    sp2.delete('page');
    const s = sp2.toString();
    router.push(`/opac/events${s ? `?${s}` : ''}`, { scroll: false });
  }

  function submit(e: React.FormEvent) {
    e.preventDefault();
    navigate({ search: q });
  }

  return (
    <div className="mx-auto max-w-6xl px-4 py-8">
      <header className="mb-6">
        <h1 className="font-serif text-2xl font-bold">Événements de la bibliothèque</h1>
        <p className="mt-1 text-muted-foreground">Découvrez les ateliers, rencontres et activités à venir</p>
      </header>

      <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
        <form onSubmit={submit} className="flex flex-1 gap-2 sm:max-w-md">
          <div className="relative flex-1">
            <Icon name="search" className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <input
              type="text"
              value={q}
              onChange={(e) => setQ(e.target.value)}
              placeholder="Rechercher un événement…"
              className="w-full rounded-lg border border-border bg-surface py-2 pl-10 pr-3 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
            />
          </div>
          <button type="submit" className="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" aria-label="Rechercher">
            <Icon name="search" className="h-4 w-4" />
          </button>
        </form>

        <div className="flex flex-wrap gap-1.5">
          {FILTERS.map((f) => (
            <button
              key={f.value}
              type="button"
              onClick={() => navigate({ filter: f.value })}
              className={`rounded-lg px-3.5 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${
                filter === f.value
                  ? 'bg-primary text-primary-foreground'
                  : 'border border-border bg-surface text-foreground hover:bg-muted'
              }`}
            >
              {f.label}
            </button>
          ))}
        </div>
      </div>

      {isError ? (
        <EmptyState title="Erreur de chargement" description="Impossible de charger les événements." action={{ href: '/opac/events', label: 'Réessayer' }} />
      ) : isLoading ? (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {Array.from({ length: 6 }).map((_, i) => (
            <div key={i} className="h-56 animate-pulse rounded-xl border border-border bg-surface" />
          ))}
        </div>
      ) : items.length === 0 ? (
        search ? (
          <EmptyState title="Aucun événement trouvé" description="Aucun événement ne correspond à votre recherche. Essayez d'autres mots-clés ou périodes." action={{ href: '/opac/events', label: 'Voir tous les événements' }} />
        ) : filter === 'past' ? (
          <EmptyState title="Aucun événement passé" description="Aucun événement passé dans nos archives." action={{ href: '/opac/search', label: 'Rechercher des documents' }} />
        ) : (
          <EmptyState title="Aucun événement à venir" description="Il n'y a actuellement aucun événement planifié. Revenez plus tard !" action={{ href: '/opac/search', label: 'Rechercher des documents' }} />
        )
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((e) => (
            <article key={e.id} className="flex flex-col rounded-xl border border-border bg-surface shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
              <div className="flex flex-1 flex-col gap-3 p-5">
                <div className="flex flex-wrap gap-1.5">
                  <span className="inline-flex items-center rounded-full bg-primary px-2.5 py-1 text-xs font-medium text-primary-foreground">
                    <Icon name="calendar" className="mr-1 h-3 w-3" />
                    {formatDateLabel(e.start_date ?? '')}
                  </span>
                  {e.end_date && e.end_date !== e.start_date && (
                    <span className="inline-flex items-center rounded-full bg-muted px-2.5 py-1 text-xs text-muted-foreground">
                      au {formatDateLabel(e.end_date)}
                    </span>
                  )}
                </div>
                <h2 className="font-serif text-lg font-semibold leading-snug">
                  <Link href={`/opac/events/${e.id}`} className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">{e.name}</Link>
                </h2>
                {e.location && (
                  <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
                    <Icon name="bookmark" className="h-3.5 w-3.5" />
                    {e.location}
                  </p>
                )}
                {e.description && <p className="line-clamp-3 text-sm text-muted-foreground">{e.description.slice(0, 160)}</p>}
              </div>
              <div className="mt-auto flex items-center justify-between border-t border-border px-5 py-3">
                <Link href={`/opac/events/${e.id}`} className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                  En savoir plus
                  <Icon name="arrowRightCircle" className="h-3.5 w-3.5" />
                </Link>
                {e.start_date && <small className="text-xs text-muted-foreground"><Icon name="clock" className="mr-1 inline h-3 w-3" />{formatTime(e.start_date)}</small>}
              </div>
            </article>
          ))}
        </div>
      )}

      {meta && meta.last_page > 1 && (
        <footer className="mt-6 text-sm text-muted-foreground">
          Affichage de {items.length} sur {meta.total} événements
        </footer>
      )}
    </div>
  );
}

export default function OpacEventsPage() {
  return (
    <Suspense fallback={<div className="mx-auto max-w-6xl px-4 py-12 text-center text-sm text-muted-foreground">Chargement…</div>}>
      <EventListInner />
    </Suspense>
  );
}
