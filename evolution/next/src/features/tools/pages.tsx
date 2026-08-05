'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useCreate } from '@/lib/api/hooks';
import { DataTable } from '@/components/ui/table';
import * as api from './services/tool.service';
import { ThesaurusViews, Barcode } from './components/tool-views';
import type { FeatureRoute } from '@/lib/routing';

function makeList(r: ResourceApi, key: string, columns: TableColumn<Entity>[], o: { title: string; create?: string }) {
  return function List() {
    const { data, isLoading, isError } = useResourceList(r, key, { 'page.size': 50 } as never);
    const destroy = useDestroy(r, key);
    const rows = (data?.data ?? []) as Entity[];
    return (
      <div className="flex h-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{o.title}</h1>
          {o.create && <Link href={o.create} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link>}
        </header>
        <DataTable columns={columns} rows={rows} loading={isLoading} error={isError}
          actions={(row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>} />
      </div>
    );
  };
}

function makeForm(r: ResourceApi, key: string, o: { title: string; back: string; fields: { name: string; label: string; required?: boolean }[] }) {
  return function Form() {
    const router = useRouter();
    const create = useCreate(r, key);
    const [v, setV] = useState<Record<string, string>>({});
    async function submit(e: React.FormEvent) {
      e.preventDefault();
      await create.mutateAsync(v);
      router.push(o.back);
    }
    return (
      <form onSubmit={submit} className="flex w-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{o.title}</h1>
          <button type="button" onClick={() => router.push(o.back)} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
        </header>
        <div className="grid max-w-2xl grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2">
          {o.fields.map((f) => (
            <label key={f.name} className="flex flex-col gap-1 text-sm">
              <span>{f.label} {f.required && <span className="text-danger">*</span>}</span>
              <input value={v[f.name] ?? ''} onChange={(e) => setV((p) => ({ ...p, [f.name]: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
            </label>
          ))}
        </div>
        <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
      </form>
    );
  };
}

const REF_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
];
const NAME_COLS: TableColumn<Entity>[] = [{ key: 'name', label: 'Nom' }];

export const routes: FeatureRoute[] = [
  { path: '/tools/activities', List: makeList(api.activitiesApi, 'activities', REF_COLS, { title: 'Plan de classement', create: '/tools/activities/create' }), Form: makeForm(api.activitiesApi, 'activities', { title: 'Classe', back: '/tools/activities', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/communicabilities', List: makeList(api.communicabilitiesApi, 'communicabilities', REF_COLS, { title: 'Communicabilité', create: '/tools/communicabilities/create' }), Form: makeForm(api.communicabilitiesApi, 'communicabilities', { title: 'Classe de communicabilité', back: '/tools/communicabilities', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/organisations', List: makeList(api.organisationsApi, 'organisations', REF_COLS, { title: 'Organigramme', create: '/tools/organisations/create' }), Form: makeForm(api.organisationsApi, 'organisations', { title: 'Unité', back: '/tools/organisations', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/reference-lists', aliases: ['/settings/reference-lists'], List: makeList(api.referenceListsApi, 'reference-lists', REF_COLS, { title: 'Domaines de valeurs', create: '/tools/reference-lists/create' }), Form: makeForm(api.referenceListsApi, 'reference-lists', { title: 'Domaine de valeurs', back: '/tools/reference-lists', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/thesaurus', List: makeList(api.thesaurusSchemesApi, 'thesaurus-schemes', REF_COLS, { title: 'Schémas de thésaurus', create: '/tools/thesaurus/create' }), Form: makeForm(api.thesaurusSchemesApi, 'thesaurus-schemes', { title: 'Schéma de thésaurus', back: '/tools/thesaurus', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/thesaurus/concepts', List: makeList(api.thesaurusConceptsApi, 'thesaurus-concepts', [{ key: 'preferred_label', label: 'Libellé' }, { key: 'language', label: 'Langue' }], { title: 'Termes du thésaurus', create: '/tools/thesaurus/concepts/create' }), Form: makeForm(api.thesaurusConceptsApi, 'thesaurus-concepts', { title: 'Terme du thésaurus', back: '/tools/thesaurus/concepts', fields: [{ name: 'preferred_label', label: 'Libellé préféré', required: true }] }) },
  { path: '/tools/languages', aliases: ['/settings/languages'], List: makeList(api.languagesApi, 'languages', REF_COLS, { title: 'Langues', create: '/tools/languages/create' }), Form: makeForm(api.languagesApi, 'languages', { title: 'Langue', back: '/tools/languages', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/sorts', aliases: ['/settings/sorts'], List: makeList(api.sortsApi, 'sorts', REF_COLS, { title: 'Sorts finaux', create: '/tools/sorts/create' }), Form: makeForm(api.sortsApi, 'sorts', { title: 'Sort final', back: '/tools/sorts', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/laws', List: makeList(api.lawsApi, 'laws', REF_COLS, { title: 'Lois', create: '/tools/laws/create' }), Form: makeForm(api.lawsApi, 'laws', { title: 'Loi', back: '/tools/laws', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/law-articles', List: makeList(api.lawArticlesApi, 'law-articles', [{ key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> }, { key: 'title', label: 'Intitulé' }], { title: 'Articles de loi', create: '/tools/law-articles/create' }), Form: makeForm(api.lawArticlesApi, 'law-articles', { title: 'Article de loi', back: '/tools/law-articles', fields: [{ name: 'code', label: 'Code' }, { name: 'title', label: 'Intitulé', required: true }] }) },
  { path: '/tools/keywords', List: makeList(api.keywordsApi, 'keywords', NAME_COLS, { title: 'Mots-clés', create: '/tools/keywords/create' }), Form: makeForm(api.keywordsApi, 'keywords', { title: 'Mot-clé', back: '/tools/keywords', fields: [{ name: 'name', label: 'Nom', required: true }] }) },

  { path: '/tools/thesaurus/hierarchy', List: () => <ThesaurusViews view="hierarchy" /> },
  { path: '/tools/thesaurus/search', List: () => <ThesaurusViews view="search" /> },
  { path: '/tools/thesaurus/export-import', List: () => <ThesaurusViews view="export-import" /> },
  { path: '/tools/barcode', List: Barcode },
];
