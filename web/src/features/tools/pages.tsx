'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useCreate, useResource } from '@/lib/api/hooks';
import { DataTable, Pagination } from '@/components/ui/table';
import * as api from './services/tool.service';
import { ThesaurusViews, Barcode } from './components/tool-views';
import { ReferenceListDetail } from './components/reference-list-detail';
import type { FeatureRoute } from '@/lib/routing';

/**
 * Liste générique : aucune action destructive ici — le bouton « Voir » ouvre la
 * fiche où l'on peut modifier et/ou supprimer.
 */
function makeList(r: ResourceApi, key: string, columns: TableColumn<Entity>[], o: { title: string; create?: string; detail?: string }) {
  return function List() {
    const [page, setPage] = useState(1);
    const { data, isLoading, isError } = useResourceList(r, key, { page, 'page.size': 20 } as never);
    const rows = (data?.data ?? []) as Entity[];
    return (
      <div className="flex h-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{o.title}</h1>
          {o.create && <Link href={o.create} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">+ Nouveau</Link>}
        </header>
        <DataTable columns={columns} rows={rows} loading={isLoading} error={isError}
          actions={o.detail ? (row) => (
            <div className="flex justify-end">
              <Link href={`${o.detail}/${row.id}`} className="rounded border border-border px-2 py-1 text-xs hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Voir</Link>
            </div>
          ) : undefined} />
        <Pagination page={page} totalPages={data?.meta?.last_page ?? 1} total={data?.meta?.total} onChange={setPage} />
      </div>
    );
  };
}

/** Fiche générique : affiche les champs et porte les actions Modifier / Supprimer. */
function makeDetail(r: ResourceApi, key: string, o: { title: string; back: string; fields: { name: string; label: string }[] }) {
  return function Detail({ id }: { id: string }) {
    const router = useRouter();
    const { data, isLoading, isError } = useResource(r, key, id);
    const destroy = useDestroy(r, key);
    if (isLoading) return <p className="text-sm text-muted-foreground">Chargement…</p>;
    if (isError) return <p className="text-sm text-danger">Impossible de charger la fiche.</p>;
    const e = (data?.data ?? {}) as Entity;
    async function onDelete() {
      if (!window.confirm(`Supprimer « ${String(e.name ?? e.code ?? o.title)} » ?`)) return;
      await destroy.mutateAsync(id);
      router.push(o.back);
    }
    return (
      <div className="flex flex-col gap-4">
        <header className="flex flex-wrap items-center justify-between gap-3">
          <h1 className="text-xl font-semibold">{String(e.name ?? e.code ?? o.title)}</h1>
          <div className="flex gap-2">
            <Link href={o.back} className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Retour</Link>
            <Link href={`${o.back}/${id}/edit`} className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Modifier</Link>
            <button type="button" onClick={onDelete} disabled={destroy.isPending} className="rounded border border-danger/40 bg-surface px-3 py-1.5 text-sm text-danger hover:bg-danger/10 disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Supprimer</button>
          </div>
        </header>
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
          {o.fields.map((f) => (
            <div key={f.name} className="rounded border border-border bg-surface p-3">
              <p className="text-xs font-semibold uppercase text-muted-foreground">{f.label}</p>
              <p className="mt-1 text-sm">{String(e[f.name] ?? '—')}</p>
            </div>
          ))}
        </div>
      </div>
    );
  };
}

function makeForm(r: ResourceApi, key: string, o: { title: string; back: string; fields: { name: string; label: string; required?: boolean }[] }) {
  return function Form({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
    const router = useRouter();
    const { data } = useResource(r, key, id);
    const create = useCreate(r, key);
    const [v, setV] = useState<Record<string, string>>({});
    if (mode === 'edit' && data?.data && Object.keys(v).length === 0) {
      const e = data.data;
      const next: Record<string, string> = {};
      for (const f of o.fields) next[f.name] = String(e[f.name] ?? '');
      setV(next);
    }
    async function submit(e: React.FormEvent) {
      e.preventDefault();
      if (mode === 'edit' && id) await r.update(id, v);
      else await create.mutateAsync(v);
      router.push(o.back);
    }
    return (
      <form onSubmit={submit} className="flex w-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{o.title}</h1>
          <button type="button" onClick={() => router.push(o.back)} className="rounded border border-border px-3 py-1.5 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Annuler</button>
        </header>
        <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
          {o.fields.map((f) => (
            <label key={f.name} className="flex flex-col gap-1 text-sm">
              <span>{f.label} {f.required && <span className="text-danger">*</span>}</span>
              <input value={v[f.name] ?? ''} onChange={(e) => setV((p) => ({ ...p, [f.name]: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" />
            </label>
          ))}
        </div>
        <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Enregistrer</button></footer>
      </form>
    );
  };
}

const REF_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
];
/** Colonnes des domaines de valeurs : le nom ouvre la fiche (gestion des valeurs). */
const REF_LIST_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/tools/reference-lists/${r.id}`} className="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">{String(r.name ?? '')}</Link> },
  { key: 'values_count', label: 'Valeurs' },
];
const NAME_COLS: TableColumn<Entity>[] = [{ key: 'name', label: 'Nom' }];

const REF_DETAIL_FIELDS = [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom' }];

export const routes: FeatureRoute[] = [
  { path: '/tools/activities', List: makeList(api.activitiesApi, 'activities', REF_COLS, { title: 'Plan de classement', create: '/tools/activities/create', detail: '/tools/activities' }), Detail: makeDetail(api.activitiesApi, 'activities', { title: 'Classe', back: '/tools/activities', fields: REF_DETAIL_FIELDS }), Form: makeForm(api.activitiesApi, 'activities', { title: 'Classe', back: '/tools/activities', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/communicabilities', List: makeList(api.communicabilitiesApi, 'communicabilities', REF_COLS, { title: 'Communicabilité', create: '/tools/communicabilities/create', detail: '/tools/communicabilities' }), Detail: makeDetail(api.communicabilitiesApi, 'communicabilities', { title: 'Classe de communicabilité', back: '/tools/communicabilities', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom' }, { name: 'description', label: 'Description' }] }), Form: makeForm(api.communicabilitiesApi, 'communicabilities', { title: 'Classe de communicabilité', back: '/tools/communicabilities', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/organisations', List: makeList(api.organisationsApi, 'organisations', REF_COLS, { title: 'Organigramme', create: '/tools/organisations/create', detail: '/tools/organisations' }), Detail: makeDetail(api.organisationsApi, 'organisations', { title: 'Unité', back: '/tools/organisations', fields: REF_DETAIL_FIELDS }), Form: makeForm(api.organisationsApi, 'organisations', { title: 'Unité', back: '/tools/organisations', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/reference-lists', aliases: ['/settings/reference-lists'], List: makeList(api.referenceListsApi, 'reference-lists', REF_LIST_COLS, { title: 'Domaines de valeurs', create: '/tools/reference-lists/create', detail: '/tools/reference-lists' }), Detail: ReferenceListDetail, Form: makeForm(api.referenceListsApi, 'reference-lists', { title: 'Domaine de valeurs', back: '/tools/reference-lists', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/thesaurus', List: makeList(api.thesaurusSchemesApi, 'thesaurus-schemes', REF_COLS, { title: 'Schémas de thésaurus', create: '/tools/thesaurus/create', detail: '/tools/thesaurus' }), Detail: makeDetail(api.thesaurusSchemesApi, 'thesaurus-schemes', { title: 'Schéma de thésaurus', back: '/tools/thesaurus', fields: REF_DETAIL_FIELDS }), Form: makeForm(api.thesaurusSchemesApi, 'thesaurus-schemes', { title: 'Schéma de thésaurus', back: '/tools/thesaurus', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/thesaurus/concepts', List: makeList(api.thesaurusConceptsApi, 'thesaurus-concepts', [{ key: 'preferred_label', label: 'Libellé' }, { key: 'language', label: 'Langue' }], { title: 'Termes du thésaurus', create: '/tools/thesaurus/concepts/create', detail: '/tools/thesaurus/concepts' }), Detail: makeDetail(api.thesaurusConceptsApi, 'thesaurus-concepts', { title: 'Terme du thésaurus', back: '/tools/thesaurus/concepts', fields: [{ name: 'preferred_label', label: 'Libellé préféré' }, { name: 'language', label: 'Langue' }] }), Form: makeForm(api.thesaurusConceptsApi, 'thesaurus-concepts', { title: 'Terme du thésaurus', back: '/tools/thesaurus/concepts', fields: [{ name: 'preferred_label', label: 'Libellé préféré', required: true }] }) },
  { path: '/tools/languages', aliases: ['/settings/languages'], List: makeList(api.languagesApi, 'languages', REF_COLS, { title: 'Langues', create: '/tools/languages/create', detail: '/tools/languages' }), Detail: makeDetail(api.languagesApi, 'languages', { title: 'Langue', back: '/tools/languages', fields: REF_DETAIL_FIELDS }), Form: makeForm(api.languagesApi, 'languages', { title: 'Langue', back: '/tools/languages', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/sorts', aliases: ['/settings/sorts'], List: makeList(api.sortsApi, 'sorts', REF_COLS, { title: 'Sorts finaux', create: '/tools/sorts/create', detail: '/tools/sorts' }), Detail: makeDetail(api.sortsApi, 'sorts', { title: 'Sort final', back: '/tools/sorts', fields: REF_DETAIL_FIELDS }), Form: makeForm(api.sortsApi, 'sorts', { title: 'Sort final', back: '/tools/sorts', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/laws', List: makeList(api.lawsApi, 'laws', REF_COLS, { title: 'Lois', create: '/tools/laws/create', detail: '/tools/laws' }), Detail: makeDetail(api.lawsApi, 'laws', { title: 'Loi', back: '/tools/laws', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom' }, { name: 'description', label: 'Description' }] }), Form: makeForm(api.lawsApi, 'laws', { title: 'Loi', back: '/tools/laws', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/law-articles', List: makeList(api.lawArticlesApi, 'law-articles', [{ key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> }, { key: 'name', label: 'Intitulé' }], { title: 'Articles de loi', create: '/tools/law-articles/create', detail: '/tools/law-articles' }), Detail: makeDetail(api.lawArticlesApi, 'law-articles', { title: 'Article de loi', back: '/tools/law-articles', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Intitulé' }] }), Form: makeForm(api.lawArticlesApi, 'law-articles', { title: 'Article de loi', back: '/tools/law-articles', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Intitulé', required: true }] }) },
  { path: '/tools/keywords', List: makeList(api.keywordsApi, 'keywords', NAME_COLS, { title: 'Mots-clés', create: '/tools/keywords/create', detail: '/tools/keywords' }), Detail: makeDetail(api.keywordsApi, 'keywords', { title: 'Mot-clé', back: '/tools/keywords', fields: [{ name: 'name', label: 'Nom' }] }), Form: makeForm(api.keywordsApi, 'keywords', { title: 'Mot-clé', back: '/tools/keywords', fields: [{ name: 'name', label: 'Nom', required: true }] }) },

  { path: '/tools/thesaurus/hierarchy', List: () => <ThesaurusViews view="hierarchy" /> },
  { path: '/tools/thesaurus/search', List: () => <ThesaurusViews view="search" /> },
  { path: '/tools/thesaurus/export-import', List: () => <ThesaurusViews view="export-import" /> },
  { path: '/tools/barcode', List: Barcode },
];
