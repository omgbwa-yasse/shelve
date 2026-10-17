'use client';

import { Suspense, useCallback, useMemo, useState } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { useQuery } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import { listPublicRecords, type PublicRecordsQuery } from '@/features/opac/services/opac.service';
import { RecordCard, EmptyState, PaginationBar } from '@/features/opac/components/opac-views';

/** Champs de la recherche avancée — reproduit `opac/search/index.blade.php`. */
const SORTS = [
  { value: '', label: 'Pertinence' },
  { value: 'title_asc', label: 'Titre (A-Z)' },
  { value: 'title_desc', label: 'Titre (Z-A)' },
  { value: 'date_desc', label: 'Date (plus récentes)' },
  { value: 'date_asc', label: 'Date (plus anciennes)' },
];

function SearchPageInner() {
  const router = useRouter();
  const sp = useSearchParams();

  const q = sp.get('q') ?? '';
  const type = sp.get('type') ?? '';
  const language = sp.get('language') ?? '';
  const dateFrom = sp.get('date_from') ?? '';
  const dateTo = sp.get('date_to') ?? '';
  const sort = sp.get('sort') ?? '';
  const page = Number(sp.get('page') ?? '1') || 1;

  const [quick, setQuick] = useState(q);
  // Champs de la recherche avancée
  const [adv, setAdv] = useState({ type, language, dateFrom, dateTo, sort });

  const hasQuery = Boolean(q) || Boolean(type) || Boolean(language) || Boolean(dateFrom) || Boolean(dateTo);

  const params = useMemo<PublicRecordsQuery>(() => {
    const p: PublicRecordsQuery = { per_page: 15, page };
    if (q) p.search = q;
    if (adv.type) p.type = adv.type;
    if (adv.language) p.language = adv.language;
    if (adv.dateFrom) p.date_from = adv.dateFrom;
    if (adv.dateTo) p.date_to = adv.dateTo;
    return p;
  }, [q, adv, page]);

  const { data, isLoading, isError } = useQuery({
    queryKey: ['opac-records', params],
    queryFn: () => listPublicRecords(params),
    enabled: hasQuery,
  });

  const applySearch = useCallback(
    (next: Record<string, string>, resetPage = true) => {
      const sp2 = new URLSearchParams(sp.toString());
      for (const [k, v] of Object.entries(next)) {
        if (v) sp2.set(k, v);
        else sp2.delete(k);
      }
      if (resetPage) sp2.delete('page');
      router.push(`/opac/search${sp2.toString() ? `?${sp2.toString()}` : ''}`, { scroll: false });
    },
    [router, sp],
  );

  function submitQuick(e: React.FormEvent) {
    e.preventDefault();
    setAdv((p) => ({ ...p, type: '', language: '', dateFrom: '', dateTo: '', sort: '' }));
    applySearch({ q: quick });
  }

  function submitAdvanced(e: React.FormEvent) {
    e.preventDefault();
    applySearch({ q, type: adv.type, language: adv.language, date_from: adv.dateFrom, date_to: adv.dateTo, sort: adv.sort });
  }

  function clearAll() {
    setQuick('');
    setAdv({ type: '', language: '', dateFrom: '', dateTo: '', sort: '' });
    router.push('/opac/search', { scroll: false });
  }

  const records = data?.data ?? [];
  const pagination = data?.pagination;

  return (
    <div>
      {/* ===================== HERO ===================== */}
      <section className="bg-gradient-to-r from-primary to-primary/85 text-primary-foreground">
        <div className="mx-auto max-w-6xl px-4 py-12 text-center">
          <h1 className="font-serif text-3xl font-bold">Rechercher dans nos collections</h1>
          <p className="mt-2 text-primary-foreground/85">
            Explorez les notices, documents et ressources de la bibliothèque
          </p>

          <form onSubmit={submitQuick} className="mx-auto mt-6 flex max-w-2xl gap-2">
            <div className="relative flex-1">
              <Icon name="search" className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <input
                type="text"
                value={quick}
                onChange={(e) => setQuick(e.target.value)}
                placeholder="Rechercher par titre, auteur, code…"
                className="w-full rounded-xl border-2 border-primary-foreground/20 bg-primary-foreground py-3 pl-10 pr-4 text-sm text-foreground focus:border-primary-foreground/60 focus:outline-none"
              />
            </div>
            <button
              type="submit"
              className="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground transition hover:brightness-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
            >
              <Icon name="search" className="h-4 w-4" />
              Rechercher
            </button>
          </form>

          {hasQuery && (
            <div className="mx-auto mt-4 flex max-w-2xl flex-wrap items-center justify-center gap-2 text-sm">
              <span className="text-primary-foreground/85">Résultats pour «&nbsp;{q || 'recherche avancée'}&nbsp;»</span>
              <button type="button" onClick={clearAll} className="inline-flex items-center gap-1 rounded-full bg-primary-foreground/15 px-3 py-1 transition hover:bg-primary-foreground/25 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
                <Icon name="close" className="h-3 w-3" />
                Effacer
              </button>
            </div>
          )}
        </div>
      </section>

      <div className="mx-auto max-w-6xl px-4 py-8">
        {!hasQuery ? (
          /* ===================== ACCUEIL SANS REQUÊTE ===================== */
          <div className="grid gap-8 lg:grid-cols-3">
            {/* Conseils de recherche */}
            <div className="lg:col-span-2">
              <div className="rounded-xl border border-border bg-surface p-6">
                <h2 className="mb-4 flex items-center gap-2 text-base font-semibold">
                  <Icon name="sparkles" className="h-4 w-4 text-warning" />
                  Conseils de recherche
                </h2>
                <div className="grid gap-4 sm:grid-cols-2">
                  <div className="text-sm">
                    <strong>Guillemets :</strong> phrases exactes
                    <br />
                    <span className="text-muted-foreground">Ex. « rapport annuel 2024 »</span>
                  </div>
                  <div className="text-sm">
                    <strong>Jokers :</strong> * pour les mots partiels
                    <br />
                    <span className="text-muted-foreground">Ex. climat* trouve climat, climatique…</span>
                  </div>
                  <div className="text-sm">
                    <strong>Booléens :</strong> AND, OR, NOT
                    <br />
                    <span className="text-muted-foreground">Ex. climat AND changement</span>
                  </div>
                  <div className="text-sm">
                    <strong>Filtres :</strong> combinez plusieurs champs
                    <br />
                    <span className="text-muted-foreground">Utilisez la recherche avancée pour plus de précision</span>
                  </div>
                </div>
              </div>

              {/* Recherche avancée */}
              <div className="mt-6 rounded-xl border border-border bg-surface p-6">
                <h2 className="mb-4 flex items-center gap-2 text-base font-semibold">
                  <Icon name="sliders" className="h-4 w-4 text-primary" />
                  Recherche avancée
                </h2>
                <form onSubmit={submitAdvanced} className="grid gap-4 sm:grid-cols-2">
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
                  <label className="flex flex-col gap-1 text-sm sm:col-span-2">
                    <span className="font-medium">Trier par</span>
                    <select
                      value={adv.sort}
                      onChange={(e) => setAdv((p) => ({ ...p, sort: e.target.value }))}
                      className="rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                      {SORTS.map((s) => (
                        <option key={s.value} value={s.value}>{s.label}</option>
                      ))}
                    </select>
                  </label>
                  <div className="flex flex-wrap gap-2 sm:col-span-2">
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
              </div>
            </div>

            {/* Accès rapides */}
            <div className="grid content-start gap-4">
              <Link href="/opac/records" className="rounded-xl border border-border bg-surface p-6 text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                <Icon name="book" className="mx-auto mb-3 h-8 w-8 text-primary" />
                <h3 className="font-semibold">Parcourir le catalogue</h3>
                <p className="mt-1 text-sm text-muted-foreground">Explorez l&apos;ensemble de la collection</p>
                <span className="mt-3 inline-block rounded-lg bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground">Parcourir</span>
              </Link>
              <Link href="/opac/news" className="rounded-xl border border-border bg-surface p-6 text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                <Icon name="newspaper" className="mx-auto mb-3 h-8 w-8 text-success" />
                <h3 className="font-semibold">Dernières actualités</h3>
                <p className="mt-1 text-sm text-muted-foreground">Suivez les nouveautés de la bibliothèque</p>
                <span className="mt-3 inline-block rounded-lg bg-success px-4 py-1.5 text-sm font-medium text-primary-foreground">Lire les actualités</span>
              </Link>
              <Link href="/opac/events" className="rounded-xl border border-border bg-surface p-6 text-center shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                <Icon name="calendar" className="mx-auto mb-3 h-8 w-8 text-info" />
                <h3 className="font-semibold">Événements</h3>
                <p className="mt-1 text-sm text-muted-foreground">Ateliers, rencontres et activités</p>
                <span className="mt-3 inline-block rounded-lg bg-info px-4 py-1.5 text-sm font-medium text-primary-foreground">Découvrir</span>
              </Link>
            </div>
          </div>
        ) : (
          /* ===================== RÉSULTATS ===================== */
          <div className="grid gap-8 lg:grid-cols-4">
            {/* Filtres */}
            <aside className="lg:col-span-1">
              <form onSubmit={submitAdvanced} className="space-y-4 rounded-xl border border-border bg-surface p-5">
                <h2 className="flex items-center gap-2 text-sm font-semibold">
                  <Icon name="sliders" className="h-4 w-4" />
                  Affiner la recherche
                </h2>
                <label className="flex flex-col gap-1 text-sm">
                  <span className="font-medium">Type de ressource</span>
                  <select
                    value={adv.type}
                    onChange={(e) => setAdv((p) => ({ ...p, type: e.target.value }))}
                    className="rounded-lg border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
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
                    className="rounded-lg border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
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
                    className="rounded-lg border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                  />
                </label>
                <label className="flex flex-col gap-1 text-sm">
                  <span className="font-medium">Date de fin</span>
                  <input
                    type="date"
                    value={adv.dateTo}
                    onChange={(e) => setAdv((p) => ({ ...p, dateTo: e.target.value }))}
                    className="rounded-lg border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                  />
                </label>
                <label className="flex flex-col gap-1 text-sm">
                  <span className="font-medium">Trier par</span>
                  <select
                    value={adv.sort}
                    onChange={(e) => setAdv((p) => ({ ...p, sort: e.target.value }))}
                    className="rounded-lg border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                  >
                    {SORTS.map((s) => (
                      <option key={s.value} value={s.value}>{s.label}</option>
                    ))}
                  </select>
                </label>
                <div className="grid gap-2">
                  <button type="submit" className="inline-flex items-center justify-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    <Icon name="search" className="h-3.5 w-3.5" />
                    Appliquer les filtres
                  </button>
                  <button type="button" onClick={clearAll} className="rounded-lg border border-border px-3 py-2 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    Effacer tout
                  </button>
                </div>
              </form>
            </aside>

            {/* Résultats */}
            <div className="lg:col-span-3">
              <div className="mb-4 flex items-center justify-between text-sm text-muted-foreground">
                <span>
                  {isLoading ? 'Chargement…' : `${pagination?.total ?? 0} résultat(s)`}
                </span>
              </div>

              {isError ? (
                <EmptyState title="Erreur lors de la recherche" description="Impossible de contacter le catalogue. Réessayez dans un instant." action={{ href: '/opac/search', label: 'Réessayer' }} />
              ) : isLoading ? (
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                  {Array.from({ length: 6 }).map((_, i) => (
                    <div key={i} className="h-56 animate-pulse rounded-xl border border-border bg-surface" />
                  ))}
                </div>
              ) : records.length === 0 ? (
                <EmptyState
                  title="Aucun document trouvé"
                  description="Aucune notice ne correspond à vos critères. Essayez d'autres mots-clés ou élargissez la période."
                  action={{ href: '/opac/search', label: 'Nouvelle recherche' }}
                />
              ) : (
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
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
                      router.push(`/opac/search?${sp2.toString()}`, { scroll: false });
                    }}
                  />
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

export default function OpacSearchPage() {
  return (
    <Suspense fallback={<div className="mx-auto max-w-6xl px-4 py-12 text-center text-sm text-muted-foreground">Chargement…</div>}>
      <SearchPageInner />
    </Suspense>
  );
}
