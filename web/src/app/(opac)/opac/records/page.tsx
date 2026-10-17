'use client';

import { useMemo, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useQuery } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import { listPublicRecords, type PublicRecordsQuery } from '@/features/opac/services/opac.service';
import { RecordCard, EmptyState, PaginationBar } from '@/features/opac/components/opac-views';

/**
 * Parcours du catalogue — reproduit `opac/records/index.blade.php` :
 * barre de recherche rapide + filtres avancés + grille de notices.
 */
function RecordsBrowser() {
  const router = useRouter();
  const sp = useSearchParams();

  const q = sp.get('q') ?? '';
  const type = sp.get('type') ?? '';
  const language = sp.get('language') ?? '';
  const dateFrom = sp.get('date_from') ?? '';
  const dateTo = sp.get('date_to') ?? '';
  const page = Number(sp.get('page') ?? '1') || 1;

  const [quick, setQuick] = useState(q);
  const [advOpen, setAdvOpen] = useState(false);
  const [adv, setAdv] = useState({ type, language, dateFrom, dateTo });

  const params = useMemo<PublicRecordsQuery>(() => {
    const p: PublicRecordsQuery = { per_page: 20, page };
    if (q) p.search = q;
    if (adv.type) p.type = adv.type;
    if (adv.language) p.language = adv.language;
    if (adv.dateFrom) p.date_from = adv.dateFrom;
    if (adv.dateTo) p.date_to = adv.dateTo;
    return p;
  }, [q, adv, page]);

  const { data, isLoading, isError } = useQuery({
    queryKey: ['opac-records-browse', params],
    queryFn: () => listPublicRecords(params),
  });

  function buildUrl(next: Record<string, string>, resetPage = true) {
    const sp2 = new URLSearchParams(sp.toString());
    for (const [k, v] of Object.entries(next)) {
      if (v) sp2.set(k, v);
      else sp2.delete(k);
    }
    if (resetPage) sp2.delete('page');
    const s = sp2.toString();
    return `/opac/records${s ? `?${s}` : ''}`;
  }

  function submitQuick(e: React.FormEvent) {
    e.preventDefault();
    router.push(buildUrl({ q: quick, type: '', language: '', date_from: '', date_to: '' }));
  }

  function submitAdvanced(e: React.FormEvent) {
    e.preventDefault();
    router.push(buildUrl({ q, ...adv }));
  }

  function clearAll() {
    setQuick('');
    setAdv({ type: '', language: '', dateFrom: '', dateTo: '' });
    router.push('/opac/records');
  }

  const records = data?.data ?? [];
  const pagination = data?.pagination;

  return (
    <div className="mx-auto max-w-6xl px-4 py-8">
      <header className="mb-6">
        <h1 className="font-serif text-2xl font-bold">Parcourir le catalogue</h1>
        <p className="mt-1 text-muted-foreground">Explorez l&apos;ensemble de la collection de documents et ressources</p>
      </header>

      {/* Barre de recherche rapide */}
      <form onSubmit={submitQuick} className="rounded-xl border border-border bg-surface p-4 shadow-sm">
        <div className="flex gap-2">
          <div className="relative flex-1">
            <Icon name="search" className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <input
              type="text"
              value={quick}
              onChange={(e) => setQuick(e.target.value)}
              placeholder="Rechercher dans les notices…"
              className="w-full rounded-lg border border-border bg-background py-2.5 pl-10 pr-3 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
            />
          </div>
          <button type="submit" className="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            Rechercher
          </button>
        </div>
        <div className="mt-3 text-center">
          <button
            type="button"
            onClick={() => setAdvOpen((v) => !v)}
            className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            <Icon name="sliders" className="h-3.5 w-3.5" />
            Options de recherche avancée
          </button>
        </div>
      </form>

      {/* Recherche avancée (repliée) */}
      {advOpen && (
        <form onSubmit={submitAdvanced} className="mt-4 rounded-xl border border-border bg-surface p-5 shadow-sm">
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label className="flex flex-col gap-1 text-sm">
              <span className="font-medium">Type de ressource</span>
              <select
                value={adv.type}
                onChange={(e) => setAdv((p) => ({ ...p, type: e.target.value }))}
                className="rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
              >
                <option value="">Tous les types</option>
                <option value="document">Document</option>
                <option value="book">Livre</option>
                <option value="archive">Archive</option>
              </select>
            </label>
            <label className="flex flex-col gap-1 text-sm">
              <span className="font-medium">Langue</span>
              <select
                value={adv.language}
                onChange={(e) => setAdv((p) => ({ ...p, language: e.target.value }))}
                className="rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
              >
                <option value="">Toutes les langues</option>
                <option value="fr">Français</option>
                <option value="en">Anglais</option>
                <option value="es">Espagnol</option>
                <option value="de">Allemand</option>
                <option value="ar">Arabe</option>
              </select>
            </label>
            <label className="flex flex-col gap-1 text-sm">
              <span className="font-medium">Date de début</span>
              <input
                type="date"
                value={adv.dateFrom}
                onChange={(e) => setAdv((p) => ({ ...p, dateFrom: e.target.value }))}
                className="rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
              />
            </label>
            <label className="flex flex-col gap-1 text-sm">
              <span className="font-medium">Date de fin</span>
              <input
                type="date"
                value={adv.dateTo}
                onChange={(e) => setAdv((p) => ({ ...p, dateTo: e.target.value }))}
                className="rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
              />
            </label>
          </div>
          <div className="mt-4 flex flex-wrap gap-2">
            <button type="submit" className="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              <Icon name="search" className="h-4 w-4" />
              Rechercher avec filtres
            </button>
            <button type="button" onClick={clearAll} className="rounded-lg border border-border px-4 py-2 text-sm font-medium hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              <Icon name="rotateCcw" className="mr-1 inline h-3.5 w-3.5" />
              Tout effacer
            </button>
          </div>
        </form>
      )}

      <div className="mt-6">
        <p className="mb-4 text-sm text-muted-foreground">
          {isLoading ? 'Chargement…' : `${pagination?.total ?? 0} notice(s)`}
        </p>

        {isError ? (
          <EmptyState title="Erreur de chargement" description="Impossible de contacter le catalogue. Réessayez dans un instant." action={{ href: '/opac/records', label: 'Réessayer' }} />
        ) : isLoading ? (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {Array.from({ length: 8 }).map((_, i) => (
              <div key={i} className="h-56 animate-pulse rounded-xl border border-border bg-surface" />
            ))}
          </div>
        ) : records.length === 0 ? (
          <EmptyState
            title="Aucune notice trouvée"
            description="Aucun document ne correspond à vos critères. Essayez d'autres mots-clés."
            action={{ href: '/opac/search', label: 'Aller à la recherche' }}
          />
        ) : (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {records.map((r) => (
              <RecordCard key={r.id} record={r} />
            ))}
          </div>
        )}

        {pagination && (
          <div className="mt-6">
            <PaginationBar
              page={page}
              lastPage={pagination.last_page}
              total={pagination.total}
              onChange={(p) => {
                const sp2 = new URLSearchParams(sp.toString());
                sp2.set('page', String(p));
                router.push(`/opac/records?${sp2.toString()}`, { scroll: false });
              }}
            />
          </div>
        )}
      </div>
    </div>
  );
}

export default function OpacRecordsPage() {
  return <RecordsBrowser />;
}
