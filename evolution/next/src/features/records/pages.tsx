'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useResource, useCreate } from '@/lib/api/hooks';
import { useQuery } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api/client';
import { DataTable, Pagination } from '@/components/ui/table';
import * as api from './services/record.service';
import { RecordsTree, DragDrop } from './components/records-views';
import type { FeatureRoute } from '@/lib/routing';

/* Usine locale : liste générique (propre à la feature Records) */
function makeList(r: ResourceApi, key: string, columns: TableColumn<Entity>[], o: { title: string; create?: string; detail?: string; search?: string }) {
  return function List() {
    const [page, setPage] = useState(1);
    const [d, setD] = useState('');
    const params: Record<string, unknown> = { page, 'page.size': 20 };
    if (d && o.search) params[`filter[${o.search}][like]`] = d;
    const { data, isLoading, isError } = useResourceList(r, key, params as never);
    const destroy = useDestroy(r, key);
    const rows = (data?.data ?? []) as Entity[];
    const m = data?.meta;
    return (
      <div className="flex h-full flex-col gap-4">
        <header className="flex flex-wrap items-center justify-between gap-3">
          <h1 className="text-xl font-semibold">{o.title}</h1>
          <div className="flex gap-2">
            {o.search && <input type="search" placeholder="Rechercher…" onChange={(e) => { setD(e.target.value); setPage(1); }} className="w-56 rounded border border-border bg-surface px-3 py-1.5 text-sm" />}
            {o.create && <Link href={o.create} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link>}
          </div>
        </header>
        <DataTable columns={columns} rows={rows} loading={isLoading} error={isError}
          actions={o.detail ? (row) => (
            <div className="flex justify-end gap-1">
              <Link href={`${o.detail}/${row.id}`} className="rounded border border-border px-2 py-1 text-xs hover:bg-muted">Voir</Link>
              <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>
            </div>
          ) : (row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>} />
        <Pagination page={page} totalPages={m?.last_page ?? 1} total={m?.total} onChange={setPage} />
      </div>
    );
  };
}

/* Usine locale : formulaire simple (propre à la feature Records) */
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
          <button type="button" onClick={() => router.push(o.back)} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
        </header>
        <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
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

const RECORD_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/records/${r.id}`} className="hover:underline">{String(r.name ?? '')}</Link> },
  { key: 'type_id', label: 'Typologie' },
  { key: 'created_at', label: 'Créée le', render: (r) => (r.created_at ? new Date(String(r.created_at)).toLocaleDateString('fr-FR') : '—') },
];
const REF_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
];

export function RecordDetail({ id }: { id: string }) {
  const { data, isLoading } = useResource(api.recordsApi, 'records', id);
  const [tab, setTab] = useState<string>('infos');
  if (isLoading) return <p className="text-sm text-muted-foreground">Chargement…</p>;
  const r = data?.data ?? {};
  return (
    <div className="flex flex-col gap-4">
      <header className="flex items-center justify-between">
        <div>
          <p className="font-mono text-xs text-muted-foreground">{String(r.code ?? '')}</p>
          <h1 className="text-xl font-semibold">{String(r.name ?? '')}</h1>
        </div>
        <div className="flex gap-2">
          <Link href={`/records/${id}/edit`} className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted">Modifier</Link>
        </div>
      </header>
      <nav className="flex gap-1 border-b border-border text-sm">
        {(['infos', 'children', 'containers', 'attachments', 'authors'] as const).map((t) => (
          <button key={t} type="button" onClick={() => setTab(t)} className={`rounded-t border-b-2 px-3 py-1.5 ${tab === t ? 'border-primary' : 'border-transparent text-muted-foreground hover:bg-muted'}`}>
            {t === 'infos' ? 'Informations' : t === 'children' ? 'Sous-notices' : t === 'containers' ? 'Contenants' : t === 'attachments' ? 'Pièces jointes' : 'Auteurs'}
          </button>
        ))}
      </nav>
      {tab === 'infos' && (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
          {([['Code', r.code], ['Nom', r.name], ['Description', r.description], ['Typologie', r.type_id], ['Niveau', r.level_id], ['Créée le', r.created_at ? new Date(String(r.created_at)).toLocaleDateString('fr-FR') : '—']] as [string, unknown][]).map(([k, v]) => (
            <div key={String(k)} className="rounded border border-border bg-surface p-3">
              <p className="text-xs font-semibold uppercase text-muted-foreground">{k}</p>
              <p className="mt-1 text-sm">{String(v ?? '—')}</p>
            </div>
          ))}
        </div>
      )}
      {tab === 'children' && <SubList title="Sous-notices" apiPath={`/api/v1/records/${id}/children`} colKey="name" />}
      {tab === 'containers' && <SubList title="Contenants" apiPath={`/api/v1/records/${id}/containers`} colKey="code" />}
      {tab === 'attachments' && <SubList title="Pièces jointes" apiPath={`/api/v1/records/${id}/attachments`} colKey="name" />}
      {tab === 'authors' && <SubList title="Auteurs" apiPath={`/api/v1/records/${id}/authors`} colKey="name" />}
    </div>
  );
}

function SubList({ title, apiPath, colKey }: { title: string; apiPath: string; colKey: string }) {
  const { data, isLoading } = useQuery({
    queryKey: [apiPath],
    queryFn: async () => (await apiFetch<{ data?: Entity[] }>(`${apiPath}?page.size=50`)).data ?? [],
  });
  const rows = data ?? [];
  return (
    <div>
      <h2 className="mb-2 text-sm font-semibold">{title}</h2>
      <DataTable columns={[{ key: colKey, label: 'Nom' }]} rows={rows} loading={isLoading} emptyLabel="Aucun élément." />
    </div>
  );
}

export const routes: FeatureRoute[] = [
  { path: '/records', List: makeList(api.recordsApi, 'records', RECORD_COLS, { title: 'Notices', create: '/records/create', detail: '/records', search: 'name' }), Detail: RecordDetail, Form: makeForm(api.recordsApi, 'records', { title: 'Notice', back: '/records', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }, { name: 'description', label: 'Description' }] }) },
  { path: '/records/trash', List: makeList(api.recordsApi, 'records-trash', RECORD_COLS, { title: 'Corbeille' }) },
  { path: '/records/authors', List: makeList(api.authorsApi, 'authors', [{ key: 'name', label: 'Nom' }, { key: 'role', label: 'Fonction' }], { title: 'Auteurs', create: '/records/authors/create', detail: '/records/authors' }), Form: makeForm(api.authorsApi, 'authors', { title: 'Auteur', back: '/records/authors', fields: [{ name: 'name', label: 'Nom', required: true }, { name: 'role', label: 'Fonction' }] }) },
  { path: '/records/author-contacts', List: makeList(api.authorContactsApi, 'author-contacts', [{ key: 'name', label: 'Nom' }, { key: 'email', label: 'Email' }], { title: 'Contacts d’auteurs', create: '/records/author-contacts/create', detail: '/records/author-contacts' }), Form: makeForm(api.authorContactsApi, 'author-contacts', { title: 'Contact d’auteur', back: '/records/author-contacts', fields: [{ name: 'name', label: 'Nom', required: true }, { name: 'email', label: 'Email' }] }) },
  { path: '/records/digital-folders', List: makeList(api.digitalFoldersApi, 'digital-folders', REF_COLS, { title: 'Dossiers numériques', create: '/records/digital-folders/create', detail: '/records/digital-folders' }), Form: makeForm(api.digitalFoldersApi, 'digital-folders', { title: 'Dossier numérique', back: '/records/digital-folders', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/records/digital-documents', List: makeList(api.digitalDocumentsApi, 'digital-documents', REF_COLS, { title: 'Documents numériques', create: '/records/digital-documents/create', detail: '/records/digital-documents' }), Form: makeForm(api.digitalDocumentsApi, 'digital-documents', { title: 'Document numérique', back: '/records/digital-documents', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },

  { path: '/tools/record-types', aliases: ['/settings/record-types'], List: makeList(api.recordTypesApi, 'record-types', REF_COLS, { title: 'Typologies de notices', create: '/tools/record-types/create', detail: '/tools/record-types' }), Form: makeForm(api.recordTypesApi, 'record-types', { title: 'Typologie', back: '/tools/record-types', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/record-statuses', aliases: ['/settings/record-statuses'], List: makeList(api.recordStatusesApi, 'record-statuses', REF_COLS, { title: 'Statuts de notices', create: '/tools/record-statuses/create' }), Form: makeForm(api.recordStatusesApi, 'record-statuses', { title: 'Statut', back: '/tools/record-statuses', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/record-supports', aliases: ['/settings/record-supports'], List: makeList(api.recordSupportsApi, 'record-supports', REF_COLS, { title: 'Supports', create: '/tools/record-supports/create' }), Form: makeForm(api.recordSupportsApi, 'record-supports', { title: 'Support', back: '/tools/record-supports', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/metadata-definitions', aliases: ['/settings/metadata-definitions'], List: makeList(api.metadataDefinitionsApi, 'metadata-definitions', [{ key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> }, { key: 'name', label: 'Nom' }, { key: 'data_type', label: 'Type' }], { title: 'Définitions de métadonnées', create: '/tools/metadata-definitions/create' }), Form: makeForm(api.metadataDefinitionsApi, 'metadata-definitions', { title: 'Définition de métadonnée', back: '/tools/metadata-definitions', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/folder-types', aliases: ['/settings/folder-types'], List: makeList(api.recordTypesApi, 'folder-types', REF_COLS, { title: 'Types de dossiers numériques', create: '/tools/folder-types/create' }), Form: makeForm(api.recordTypesApi, 'folder-types', { title: 'Type de dossier', back: '/tools/folder-types', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/document-types', aliases: ['/settings/document-types'], List: makeList(api.recordTypesApi, 'document-types', REF_COLS, { title: 'Types de documents numériques', create: '/tools/document-types/create' }), Form: makeForm(api.recordTypesApi, 'document-types', { title: 'Type de document', back: '/tools/document-types', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },

  { path: '/records/tree', List: RecordsTree },
  { path: '/records/drag-drop', List: DragDrop },
];
