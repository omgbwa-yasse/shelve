'use client';

import { Suspense, useMemo, useState } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { useQuery } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import { listPublicNews, type PublicNewsQuery } from '@/features/opac/services/opac.service';
import { EmptyState, OpacDate } from '@/features/opac/components/opac-views';

function stripHtml(html: string): string {
  return html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}

function NewsListInner() {
  const router = useRouter();
  const sp = useSearchParams();
  const search = sp.get('search') ?? '';
  const page = Number(sp.get('page') ?? '1') || 1;

  const [q, setQ] = useState(search);

  const params = useMemo<PublicNewsQuery>(() => ({ per_page: 12, page, ...(search ? { search } : {}) }), [search, page]);

  const { data, isLoading, isError } = useQuery({
    queryKey: ['opac-news', params],
    queryFn: () => listPublicNews(params),
  });

  const items = data?.data ?? [];
  const meta = data?.meta;

  function submit(e: React.FormEvent) {
    e.preventDefault();
    const sp2 = new URLSearchParams(sp.toString());
    if (q) sp2.set('search', q);
    else sp2.delete('search');
    sp2.delete('page');
    router.push(`/opac/news${sp2.toString() ? `?${sp2.toString()}` : ''}`, { scroll: false });
  }

  return (
    <div className="mx-auto max-w-6xl px-4 py-8">
      <header className="mb-6">
        <h1 className="font-serif text-2xl font-bold">Actualités de la bibliothèque</h1>
        <p className="mt-1 text-muted-foreground">Restez informé des dernières nouvelles, événements et annonces</p>
      </header>

      <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
        <form onSubmit={submit} className="flex flex-1 gap-2 sm:max-w-md">
          <div className="relative flex-1">
            <Icon name="search" className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <input
              type="text"
              value={q}
              onChange={(e) => setQ(e.target.value)}
              placeholder="Rechercher une actualité…"
              className="w-full rounded-lg border border-border bg-surface py-2 pl-10 pr-3 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
            />
          </div>
          <button type="submit" className="rounded-lg border border-border px-4 py-2 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" aria-label="Rechercher">
            <Icon name="search" className="h-4 w-4" />
          </button>
        </form>
        <small className="text-muted-foreground">{meta?.total ?? 0} article(s) trouvé(s)</small>
      </div>

      {isError ? (
        <EmptyState title="Erreur de chargement" description="Impossible de charger les actualités." action={{ href: '/opac/news', label: 'Réessayer' }} />
      ) : isLoading ? (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {Array.from({ length: 6 }).map((_, i) => (
            <div key={i} className="h-64 animate-pulse rounded-xl border border-border bg-surface" />
          ))}
        </div>
      ) : items.length === 0 ? (
        search ? (
          <EmptyState title="Aucune actualité trouvée" description="Aucun article ne correspond à votre recherche. Essayez d'autres mots-clés." action={{ href: '/opac/news', label: 'Voir toutes les actualités' }} />
        ) : (
          <EmptyState title="Aucune actualité disponible" description="Il n'y a actuellement aucun article publié." action={{ href: '/opac/search', label: 'Rechercher des documents' }} />
        )
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((n) => (
            <article key={n.id} className="flex flex-col rounded-xl border border-border bg-surface shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
              <div className="flex flex-1 flex-col gap-3 p-5">
                <span className="inline-flex w-fit items-center rounded-full bg-primary px-2.5 py-1 text-xs font-medium text-primary-foreground">
                  <OpacDate value={n.published_at ?? n.created_at} />
                </span>
                <h2 className="font-serif text-lg font-semibold leading-snug">
                  <Link href={`/opac/news/${n.id}`} className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">{n.title}</Link>
                </h2>
                {n.summary && <p className="text-sm text-muted-foreground">{n.summary.slice(0, 160)}</p>}
                {n.content && !n.summary && <p className="line-clamp-3 text-sm text-muted-foreground">{stripHtml(n.content).slice(0, 220)}</p>}
              </div>
              <div className="mt-auto flex items-center justify-between border-t border-border px-5 py-3">
                <Link href={`/opac/news/${n.id}`} className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                  Lire la suite
                  <Icon name="arrowRightCircle" className="h-3.5 w-3.5" />
                </Link>
                {n.author && <small className="text-xs text-muted-foreground">{n.author.name}</small>}
              </div>
            </article>
          ))}
        </div>
      )}

      {meta && meta.last_page > 1 && (
        <footer className="mt-6 flex items-center justify-between text-sm text-muted-foreground">
          <span>Page {meta.current_page} sur {meta.last_page}</span>
          <div className="flex gap-2">
            <button
              type="button"
              disabled={meta.current_page <= 1}
              onClick={() => { const s = new URLSearchParams(sp.toString()); s.set('page', String(meta.current_page - 1)); router.push(`/opac/news?${s.toString()}`); }}
              className="rounded-lg border border-border bg-surface px-3 py-1.5 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              Précédent
            </button>
            <button
              type="button"
              disabled={meta.current_page >= meta.last_page}
              onClick={() => { const s = new URLSearchParams(sp.toString()); s.set('page', String(meta.current_page + 1)); router.push(`/opac/news?${s.toString()}`); }}
              className="rounded-lg border border-border bg-surface px-3 py-1.5 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              Suivant
            </button>
          </div>
        </footer>
      )}
    </div>
  );
}

export default function OpacNewsPage() {
  return (
    <Suspense fallback={<div className="mx-auto max-w-6xl px-4 py-12 text-center text-sm text-muted-foreground">Chargement…</div>}>
      <NewsListInner />
    </Suspense>
  );
}
