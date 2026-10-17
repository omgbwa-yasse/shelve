'use client';

import { useEffect, useRef, useState } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import clsx from 'clsx';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useResource, useCreate } from '@/lib/api/hooks';
import { apiFetch } from '@/lib/api/client';
import { DataTable, Pagination } from '@/components/ui/table';
import { UniversalModal } from '@/components/ui/UniversalModal';
import { Icon, type IconKey } from '@/components/icons';
import * as api from './services/record.service';
import { ACCESS_LEVEL_LABELS } from './services/record.service';
import { RecordsTree, DragDrop } from './components/records-views';
import { recordLevelsApi, recordConfidentialitiesApi, MetadataFieldsSection } from './components/record-form-fields';
import { containersApi } from '@/features/deposits/services/deposit.service';
import { usersApi } from '@/features/settings/services/setting.service';
import { activitiesApi, keywordsApi, organisationsApi } from '@/features/tools/services/tool.service';
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
            {o.search && <input type="search" placeholder="Rechercher…" onChange={(e) => { setD(e.target.value); setPage(1); }} className="w-56 rounded border border-border bg-surface px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" />}
            {o.create && <Link href={o.create} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">+ Nouveau</Link>}
          </div>
        </header>
        <DataTable columns={columns} rows={rows} loading={isLoading} error={isError}
          actions={o.detail ? (row) => (
            <div className="flex justify-end gap-1">
              <Link href={`${o.detail}/${row.id}`} className="rounded px-2 py-1 text-xs hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Voir</Link>
              <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded px-2 py-1 text-xs text-danger hover:bg-danger/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Supprimer</button>
            </div>
          ) : (row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded px-2 py-1 text-xs text-danger hover:bg-danger/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Supprimer</button>} />
        <Pagination page={page} totalPages={m?.last_page ?? 1} total={m?.total} onChange={setPage} />
      </div>
    );
  };
}

/* Usine locale : formulaire simple (propre à la feature Records) — référentiels seulement. */
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

function labelOf(v: unknown, field: string): string {
  const o = v as Entity | undefined | null;
  if (!o) return '—';
  return String(o[field] ?? o.name ?? o.code ?? '—');
}

const RECORD_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/records/${r.id}`} className="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">{String(r.name ?? '')}</Link> },
  { key: 'type', label: 'Typologie', render: (r) => labelOf(r.type, 'name') },
  { key: 'level', label: 'Niveau', render: (r) => labelOf(r.level, 'name') },
  { key: 'status', label: 'Statut', render: (r) => labelOf(r.status, 'name') },
  { key: 'created_at', label: 'Créée le', render: (r) => (r.created_at ? new Date(String(r.created_at)).toLocaleDateString('fr-FR') : '—') },
];
const REF_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
];

/* ---------------------------------------------------------------------------
 * Liste des notices — passe par `getRecords` (avec `include`) plutôt que le
 * `recordsApi.list` générique, pour que Typologie/Niveau/Statut affichent un
 * libellé au lieu d'un identifiant brut (voir RecordResource::whenLoaded).
 * ------------------------------------------------------------------------- */
/* ---------------------------------------------------------------------------
 * « Mes documents » — page unique à onglets (Organisations, Plan de classement,
 * Dossiers/Documents récents, Récents, Activités, Auteurs, Dates, Mots-clés,
 * Corbeille) avec affichage en Arborescence (défaut), Grille ou Liste.
 * ------------------------------------------------------------------------- */
type TabKey = 'organisations' | 'plan' | 'folders' | 'documents' | 'recent' | 'activities' | 'authors' | 'dates' | 'keywords' | 'trash';
type ViewMode = 'tree' | 'grid' | 'list';

const MD_TABS: { key: TabKey; label: string; icon: IconKey }[] = [
  { key: 'organisations', label: 'Organisations', icon: 'users' },
  { key: 'plan', label: 'Plan de classement', icon: 'listCheck' },
  { key: 'folders', label: 'Dossiers récents', icon: 'folderOpen' },
  { key: 'documents', label: 'Documents récents', icon: 'fileText' },
  { key: 'recent', label: 'Récents', icon: 'clock' },
  { key: 'activities', label: 'Activités', icon: 'listTodo' },
  { key: 'authors', label: 'Auteurs', icon: 'person' },
  { key: 'dates', label: 'Dates', icon: 'calendar' },
  { key: 'keywords', label: 'Mots-clés', icon: 'tags' },
  { key: 'trash', label: 'Corbeille', icon: 'trash' },
];

type TNode = { key: string; label: React.ReactNode; recordId?: number; children: TNode[] };

/** Arborescence générique (nœuds dépliables), feuilles = liens vers la fiche. */
function TreeView({ nodes }: { nodes: TNode[] }) {
  const [closed, setClosed] = useState<Set<string>>(new Set());
  function toggle(key: string) {
    setClosed((p) => {
      const n = new Set(p);
      if (n.has(key)) n.delete(key); else n.add(key);
      return n;
    });
  }
  function render(ns: TNode[], depth: number): React.ReactNode {
    return ns.map((n) => {
      const isClosed = closed.has(n.key);
      const hasKids = n.children.length > 0;
      return (
        <div key={n.key}>
          <div className="flex items-center gap-1 rounded px-1 py-0.5 hover:bg-muted" style={{ paddingLeft: depth * 20 }}>
            {hasKids ? (
              <button
                type="button"
                onClick={() => toggle(n.key)}
                className="flex h-4 w-4 items-center justify-center rounded text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                title={isClosed ? 'Dérouler' : 'Réduire'}
                aria-expanded={!isClosed}
              >
                <Icon name={isClosed ? 'chevronRight' : 'chevronDown'} className="h-3.5 w-3.5" />
              </button>
            ) : (
              <span className="flex h-4 w-4 items-center justify-center text-xs text-muted-foreground/40">·</span>
            )}
            {n.recordId ? <Link href={`/records/${n.recordId}`} className="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">{n.label}</Link> : <span>{n.label}</span>}
          </div>
          {hasKids && !isClosed && render(n.children, depth + 1)}
        </div>
      );
    });
  }
  return (
    <div className="min-h-0 flex-1 overflow-auto rounded bg-surface p-3 text-sm">
      {render(nodes, 0)}
      {nodes.length === 0 && <p className="text-muted-foreground">Aucun résultat.</p>}
    </div>
  );
}

/** Grille de cartes (code, nom, typologie, statut, date). */
function GridView({ records }: { records: Entity[] }) {
  return (
    <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      {records.map((r) => (
        <Link key={String(r.id)} href={`/records/${r.id}`} className="rounded bg-surface p-3 hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
          <p className="font-mono text-xs text-muted-foreground">{String(r.code ?? '')}</p>
          <p className="mt-1 line-clamp-2 font-medium">{String(r.name ?? '')}</p>
          <p className="mt-1 text-xs text-muted-foreground">{labelOf(r.type, 'name')} · {labelOf(r.status, 'name')}</p>
          <p className="text-xs text-muted-foreground">{r.created_at ? new Date(String(r.created_at)).toLocaleDateString('fr-FR') : '—'}</p>
        </Link>
      ))}
      {records.length === 0 && <p className="text-muted-foreground">Aucun résultat.</p>}
    </div>
  );
}

/** Arbre de notices parent/enfant (dossiers, documents, corbeille). */
function buildRecordTree(records: Entity[]): TNode[] {
  const byParent = new Map<string | null, Entity[]>();
  for (const r of records) {
    const k = r.parent_id == null ? null : String(r.parent_id);
    if (!byParent.has(k)) byParent.set(k, []);
    byParent.get(k)!.push(r);
  }
  const node = (r: Entity): TNode => ({
    key: `r-${r.id}`,
    label: <><span className="font-mono text-xs text-muted-foreground">{String(r.code ?? '')}</span> {String(r.name ?? '')}</>,
    recordId: Number(r.id),
    children: (byParent.get(String(r.id)) ?? []).map(node),
  });
  return (byParent.get(null) ?? []).map(node);
}

/** Arbre de groupes (organisations / activités) avec les notices en feuilles. */
function buildGroupTree(groups: Entity[], records: Entity[], field: 'organisation_id' | 'activity_id', prefix: string): TNode[] {
  const byParent = new Map<string | null, Entity[]>();
  for (const g of groups) {
    const k = g.parent_id == null ? null : String(g.parent_id);
    if (!byParent.has(k)) byParent.set(k, []);
    byParent.get(k)!.push(g);
  }
  const node = (g: Entity): TNode => {
    const kids = (byParent.get(String(g.id)) ?? []).map(node);
    const recs = records
      .filter((r) => r[field] != null && String(r[field]) === String(g.id))
      .map((r): TNode => ({
        key: `${prefix}-${g.id}-r-${r.id}`,
        label: <><span className="font-mono text-xs text-muted-foreground">{String(r.code ?? '')}</span> {String(r.name ?? '')}</>,
        recordId: Number(r.id),
        children: [],
      }));
    return {
      key: `${prefix}-${g.id}`,
      label: <>{String(g.name ?? g.code ?? g.id)} <span className="text-xs text-muted-foreground">({recs.length})</span></>,
      children: [...kids, ...recs],
    };
  };
  return (byParent.get(null) ?? []).map(node);
}

/** Table des auteurs (onglet Auteurs de « Mes documents »). */
function AuthorTable({ authors }: { authors: Entity[] }) {
  const cols: TableColumn<Entity>[] = [
    { key: 'name', label: 'Nom', render: (r) => <Link href={`/records/authors/${r.id}`} className="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">{String(r.name ?? '')}</Link> },
    { key: 'type_id', label: 'Type' },
    { key: 'lifespan', label: 'Durée de vie' },
    { key: 'parent', label: 'Parent', render: (r) => String((r.parent as { name?: string } | undefined)?.name ?? '—') },
  ];
  return <DataTable columns={cols} rows={authors} loading={false} error={false} />;
}

function MesDocuments() {
  const [tab, setTab] = useState<TabKey>('organisations');
  const [view, setView] = useState<ViewMode>('tree');
  const [selAct, setSelAct] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');
  const [kw, setKw] = useState('');

  const recordsQ = useQuery({
    queryKey: ['md-records', tab === 'keywords' ? kw : ''],
    queryFn: () => api.getRecords({ 'page.size': 300, include: 'type,level,status', ...(tab === 'keywords' && kw ? { 'filter[keyword][like]': kw } : {}) }),
  });
  const trashQ = useQuery({
    queryKey: ['md-trash'],
    queryFn: () => api.getRecordsTrash({ 'page.size': 300 }),
  });
  const orgsQ = useQuery({
    queryKey: ['md-organisations'],
    queryFn: async () => (await organisationsApi.list({ 'page.size': 300 })) as { data: Entity[] },
  });
  const actsQ = useQuery({
    queryKey: ['md-activities'],
    queryFn: async () => (await activitiesApi.list({ 'page.size': 300 })) as { data: Entity[] },
  });
  const authorsQ = useQuery({
    queryKey: ['md-authors'],
    queryFn: async () => (await api.authorsApi.list({ 'page.size': 300 })) as { data: Entity[] },
  });
  const keywordsQ = useQuery({
    queryKey: ['md-keywords'],
    queryFn: async () => (await keywordsApi.list({ 'page.size': 300 })) as { data: Entity[] },
  });

  const allRecords = (recordsQ.data?.data ?? []) as Entity[];
  const trash = (trashQ.data?.data ?? []) as Entity[];
  const orgs = (orgsQ.data?.data ?? []) as Entity[];
  const acts = (actsQ.data?.data ?? []) as Entity[];
  const authors = (authorsQ.data?.data ?? []) as Entity[];
  const keywords = (keywordsQ.data?.data ?? []) as Entity[];

  const folders = allRecords.filter((r) => Boolean(r.is_container)).sort((a, b) => String(b.created_at ?? '').localeCompare(String(a.created_at ?? '')));
  const documents = allRecords.filter((r) => !r.is_container).sort((a, b) => String(b.created_at ?? '').localeCompare(String(a.created_at ?? '')));
  const recent = [...allRecords].sort((a, b) => String(b.created_at ?? '').localeCompare(String(a.created_at ?? '')));
  const actFiltered = selAct ? allRecords.filter((r) => r.activity_id != null && String(r.activity_id) === selAct) : allRecords;
  const dateFiltered = allRecords.filter((r) => {
    if (!dateFrom && !dateTo) return true;
    const d = String(r.start_date ?? '');
    if (!d) return false;
    if (dateFrom && d < dateFrom) return false;
    if (dateTo && d > dateTo) return false;
    return true;
  });

  function toolbar(): React.ReactNode {
    if (tab === 'activities') {
      return (
        <div className="flex items-center gap-2">
          <Icon name="listTodo" className="h-4 w-4 text-muted-foreground" />
          <select value={selAct} onChange={(e) => setSelAct(e.target.value)} className="w-64 rounded border border-border bg-surface px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
            <option value="">Toutes les activités</option>
            {acts.map((a) => <option key={String(a.id)} value={String(a.id)}>{String(a.code ? `${a.code} — ` : '')}{String(a.name ?? a.id)}</option>)}
          </select>
        </div>
      );
    }
    if (tab === 'dates') {
      return (
        <div className="flex items-center gap-2">
          <Icon name="calendar" className="h-4 w-4 text-muted-foreground" />
          <label className="text-sm text-muted-foreground">Du</label>
          <input type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} className="rounded border border-border bg-surface px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" />
          <label className="text-sm text-muted-foreground">Au</label>
          <input type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} className="rounded border border-border bg-surface px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" />
        </div>
      );
    }
    if (tab === 'keywords') {
      return (
        <div className="flex items-center gap-2">
          <Icon name="tags" className="h-4 w-4 text-muted-foreground" />
          <select value={kw} onChange={(e) => setKw(e.target.value)} className="w-64 rounded border border-border bg-surface px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
            <option value="">Tous les mots-clés</option>
            {keywords.map((k) => <option key={String(k.id)} value={String(k.name ?? k.code ?? '')}>{String(k.code ? `${k.code} — ` : '')}{String(k.name ?? k.id)}</option>)}
          </select>
        </div>
      );
    }
    return null;
  }

  function content(): React.ReactNode {
    switch (tab) {
      case 'organisations':
        return view === 'tree' ? <TreeView nodes={buildGroupTree(orgs, allRecords, 'organisation_id', 'o')} /> : <GridView records={allRecords} />;
      case 'plan':
        return view === 'tree' ? <TreeView nodes={buildGroupTree(acts, allRecords, 'activity_id', 'a')} /> : <GridView records={allRecords} />;
      case 'folders':
        return view === 'tree' ? <TreeView nodes={buildRecordTree(folders)} /> : <GridView records={folders} />;
      case 'documents':
        return view === 'tree' ? <TreeView nodes={buildRecordTree(documents)} /> : <GridView records={documents} />;
      case 'recent':
        return view === 'tree' ? <TreeView nodes={buildRecordTree(recent)} /> : <GridView records={recent} />;
      case 'activities':
        return view === 'tree' ? <TreeView nodes={buildGroupTree(acts, actFiltered, 'activity_id', 'a')} /> : <GridView records={actFiltered} />;
      case 'dates':
        return view === 'tree' ? <TreeView nodes={buildRecordTree(dateFiltered)} /> : <GridView records={dateFiltered} />;
      case 'keywords':
        return view === 'tree' ? <TreeView nodes={buildRecordTree(allRecords)} /> : <GridView records={allRecords} />;
      case 'trash':
        return view === 'tree' ? <TreeView nodes={buildRecordTree(trash)} /> : <GridView records={trash} />;
    }
  }

  const loading = recordsQ.isLoading || trashQ.isLoading || orgsQ.isLoading || actsQ.isLoading || authorsQ.isLoading || keywordsQ.isLoading;

  const listRows = tab === 'folders' ? folders
    : tab === 'documents' ? documents
    : tab === 'trash' ? trash
    : tab === 'recent' ? recent
    : tab === 'activities' ? actFiltered
    : tab === 'dates' ? dateFiltered
    : allRecords;

  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-xl font-semibold">Mes documents</h1>
        <div className="flex items-center gap-1 rounded p-1 text-sm">
          {([
            ['tree', 'listOrdered', 'Arborescence'],
            ['grid', 'layoutTemplate', 'Grille'],
            ['list', 'list', 'Liste'],
          ] as [ViewMode, IconKey, string][]).map(([m, icon, label]) => (
            <button
              key={m}
              type="button"
              onClick={() => setView(m)}
              className={clsx('flex items-center gap-1.5 rounded px-3 py-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary', view === m ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted')}
            >
              <Icon name={icon} className="h-4 w-4" />
              {label}
            </button>
          ))}
        </div>
      </header>
      <div className="flex flex-wrap gap-1">
        {MD_TABS.map((t) => (
          <button
            key={t.key}
            type="button"
            onClick={() => setTab(t.key)}
            className={clsx('flex items-center gap-1.5 rounded px-3 py-1.5 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary', tab === t.key ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted')}
          >
            <Icon name={t.icon} className="h-4 w-4" />
            {t.label}
          </button>
        ))}
      </div>
      {tab === 'authors' ? (
        <AuthorTable authors={authors} />
      ) : loading ? (
        <p className="text-sm text-muted-foreground">Chargement…</p>
      ) : (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
          {toolbar()}
          {view === 'list' ? <DataTable columns={RECORD_COLS} rows={listRows} loading={false} error={false} /> : content()}
        </div>
      )}
    </div>
  );
}

/* ---------------------------------------------------------------------------
 * Corbeille — vraie liste des notices supprimées (voir gap A.05) : jusqu'ici
 * elle tapait le même endpoint que la liste principale (le `key` du client
 * générique n'est qu'une clé de cache, pas l'URL réellement appelée).
 * ------------------------------------------------------------------------- */
function RecordTrash() {
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const { data, isLoading, isError } = useQuery({
    queryKey: ['records-trash', page],
    queryFn: () => api.getRecordsTrash({ page, 'page.size': 20 }),
  });
  const rows = (data?.data ?? []) as Entity[];
  const m = data?.meta;

  async function restore(id: string | number) {
    await api.restoreRecord(id);
    queryClient.invalidateQueries({ queryKey: ['records-trash'] });
  }

  async function forceDelete(id: string | number, name: string) {
    if (!window.confirm(`Supprimer définitivement « ${name} » ? Cette action est irréversible.`)) return;
    await api.forceDeleteRecord(id);
    queryClient.invalidateQueries({ queryKey: ['records-trash'] });
  }

  return (
    <div className="flex h-full flex-col gap-4">
      <header>
        <h1 className="text-xl font-semibold">Corbeille</h1>
        <p className="text-sm text-muted-foreground">Notices supprimées — à restaurer, ou à supprimer définitivement.</p>
      </header>
      <DataTable
        columns={[
          { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
          { key: 'name', label: 'Nom' },
          { key: 'type', label: 'Typologie', render: (r) => labelOf(r.type, 'name') },
        ]}
        rows={rows}
        loading={isLoading}
        error={isError}
        emptyLabel="La corbeille est vide."
        actions={(row) => (
          <div className="flex justify-end gap-1">
            <button type="button" onClick={() => restore(row.id as string | number)} className="rounded px-2 py-1 text-xs hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Restaurer</button>
            <button type="button" onClick={() => forceDelete(row.id as string | number, String(row.name))} className="rounded px-2 py-1 text-xs text-danger hover:bg-danger/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Supprimer définitivement</button>
          </div>
        )}
      />
      <Pagination page={page} totalPages={m?.last_page ?? 1} total={m?.total} onChange={setPage} />
    </div>
  );
}

/* ---------------------------------------------------------------------------
 * Formulaire de notice complet — couvre les champs de `StoreRecordRequest`
 * (structure, cycle de vie, confidentialité, prêt) plus la section
 * métadonnées dynamiques du type (voir `MetadataFieldsSection`).
 * ------------------------------------------------------------------------- */
function Field({ label, value, onChange, type = 'text', options, help, disabled }: {
  label: string; value?: string; onChange: (v: string) => void; type?: string;
  options?: { value: string; label: string; parent_id?: string | null }[]; help?: string; disabled?: boolean;
}) {
  const [pickerOpen, setPickerOpen] = useState(false);
  const current = options?.find((o) => o.value === value);
  // Données hiérarchiques (présence d'au moins un parent) → affichage en arbre
  // dépliable (`+`/`−`) plutôt qu'en liste plate.
  const hierarchical = (options ?? []).some((o) => o.parent_id != null && o.parent_id !== '');

  if (options) {
    return (
      <div className="flex flex-col gap-1 text-sm">
        <span>{label}</span>
        <button
          type="button"
          disabled={disabled}
          onClick={() => setPickerOpen(true)}
          className="flex items-center justify-between gap-2 rounded border border-border bg-background px-2 py-1.5 text-sm disabled:bg-muted disabled:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          <span className={current ? '' : 'text-muted-foreground'}>{current?.label ?? '—'}</span>
          <Icon name="chevronDown" className="h-4 w-4 shrink-0 text-muted-foreground" />
        </button>
        {help && <span className="text-xs text-muted-foreground">{help}</span>}
        <UniversalModal<{ value: string; label: string; parent_id?: string | null }>
          open={pickerOpen}
          onClose={() => setPickerOpen(false)}
          title={label}
          display={hierarchical ? 'tree' : 'table'}
          columns={hierarchical ? undefined : [{ key: 'label', label: label, render: (o) => <span>{o.label}</span> }]}
          rows={options}
          rowKey={(o) => o.value}
          treeParentField="parent_id"
          filter={(o, q) => o.label.toLowerCase().includes(q.trim().toLowerCase())}
          initialSelected={value ? [value] : undefined}
          onSelect={(sel) => {
            const picked = sel[0];
            onChange(picked?.value ?? '');
            setPickerOpen(false);
          }}
        />
      </div>
    );
  }

  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>{label}</span>
      {type === 'checkbox' ? (
        <input type="checkbox" checked={value === '1' || value === 'true'} onChange={(e) => onChange(e.target.checked ? '1' : '0')} className="h-4 w-4" />
      ) : (
        <input type={type} value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" />
      )}
      {help && <span className="text-xs text-muted-foreground">{help}</span>}
    </label>
  );
}

function useOptions(fetcher: () => Promise<{ data?: Entity[] }>, key: string, labelField = 'name') {
  const { data } = useQuery({ queryKey: [key], queryFn: fetcher });
  return (data?.data ?? []).map((o) => ({
    value: String(o.id),
    label: String(o[labelField] ?? o.name ?? o.code ?? o.id),
    parent_id: o.parent_id != null && o.parent_id !== '' ? String(o.parent_id) : null,
  }));
}

/** Options de typologies pour la modale universelle, filtrées par nature de notice. */
function typePickerRows(types: Entity[], kind: 'folder' | 'document'): Entity[] {
  return types
    .filter((t) => Boolean(t.is_container) === (kind === 'folder'))
    .sort((a, b) => String(a.name ?? '').localeCompare(String(b.name ?? '')));
}

const TYPE_PICKER_PAGE_SIZE = 24;
const TYPE_PICKER_SEARCH_MIN_CHARS = 3;

/** Modale universelle pré-configurée pour le choix d'une typologie de notice. */
export function RecordUniversalModal({ kind, onClose, onSelect }: {
  kind: 'folder' | 'document'; onClose: () => void; onSelect: (typeId: string) => void;
}) {
  const { data, isLoading } = useQuery({
    queryKey: ['record-types-picker', kind],
    queryFn: () => api.recordTypesApi.list({ 'page.size': 200, 'filter[is_container][eq]': kind === 'folder' ? '1' : '0' } as never),
  });
  const types = ((data?.data ?? []) as Entity[]).filter((t) => Boolean(t.is_container) === (kind === 'folder'));
  return (
    <UniversalModal<Entity>
      open
      onClose={onClose}
      title={kind === 'folder' ? 'Choisir une typologie de dossier' : 'Choisir une typologie de document'}
      rows={typePickerRows(types, kind)}
      loading={isLoading}
      rowKey={(t) => String(t.id)}
      cardTitleField="name"
      cardSubtitleField="description"
      searchMinChars={TYPE_PICKER_SEARCH_MIN_CHARS}
      alphabet
      pageSize={TYPE_PICKER_PAGE_SIZE}
      emptyLabel={`Aucune typologie ${kind === 'folder' ? 'de dossier' : 'de document'} configurée.`}
      onSelect={(sel) => onSelect(String(sel[0]?.id ?? ''))}
    />
  );
}


export function RecordForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const searchParams = useSearchParams();
  const kind = mode === 'create' ? searchParams.get('kind') : null; // 'folder' | 'document' | null
  const lockedTypeId = mode === 'create' ? (searchParams.get('type_id') ?? '') : '';
  const { data } = useResource(api.recordsApi, 'records', id);
  const create = useCreate(api.recordsApi, 'records');
  const [v, setV] = useState<Record<string, string>>(mode === 'create' ? { parent_id: searchParams.get('parent_id') ?? '', type_id: lockedTypeId } : {});
  const [metadata, setMetadata] = useState<Record<string, string>>({});
  const [loaded, setLoaded] = useState(false);
  // Dossier / Document : la modale de choix de typologie s'ouvre d'abord.
  // Ouverture différée (useEffect) pour éviter un mismatch d'hydration : la
  // modale est un portal (`document.body`) qui n'existe pas côté serveur.
  const [typeModalOpen, setTypeModalOpen] = useState<boolean>(false);
  const [typeModalReady, setTypeModalReady] = useState(false);
  useEffect(() => {
    setTypeModalReady(true);
  }, []);
  // Ouvre la modale de typologie à l'arrivée sur `?kind=folder|document` sans
  // `type_id` — y compris lors d'une navigation depuis le menu alors qu'on est
  // déjà sur `/records/create` (le composant ne se remonte pas, l'URL change).
  // Réinitialise aussi la sélection locale si on change de kind sans type figé.
  useEffect(() => {
    if (!typeModalReady) return;
    if (mode === 'create' && (kind === 'folder' || kind === 'document')) {
      if (!lockedTypeId) {
        setTypeModalOpen(true);
        setV((prev) => (prev.type_id ? { ...prev, type_id: '' } : prev));
      }
    }
  }, [typeModalReady, mode, kind, lockedTypeId]);

  if (mode === 'edit' && data?.data && !loaded) {
    const e = data.data;
    setV({
      name: String(e.name ?? ''), code: String(e.code ?? ''), description: String(e.description ?? ''),
      type_id: String(e.type_id ?? ''), level_id: String(e.level_id ?? ''), status_id: String(e.status_id ?? ''),
      activity_id: String(e.activity_id ?? ''), parent_id: String(e.parent_id ?? ''), assigned_to: String(e.assigned_to ?? ''),
      access_level: String(e.access_level ?? ''), requires_approval: e.requires_approval ? '1' : '0',
      confidentiality_id: String(e.confidentiality_id ?? ''), access_limit_id: String(e.access_limit_id ?? ''),
      start_date: String(e.start_date ?? ''), end_date: String(e.end_date ?? ''), date_exact: String(e.date_exact ?? ''),
    });
    setLoaded(true);
  }

  const { data: typesData } = useQuery({ queryKey: ['record-types-all'], queryFn: () => api.recordTypesApi.list({ 'page.size': 200 } as never) });
  const allTypes = (typesData?.data ?? []) as Entity[];
  const folderTypeOptions = allTypes.filter((t) => t.is_container).map((t) => ({ value: String(t.id), label: String(t.name) }));
  const documentTypeOptions = allTypes.filter((t) => !t.is_container).map((t) => ({ value: String(t.id), label: String(t.name) }));
  const typeOptions = kind === 'folder' ? folderTypeOptions : kind === 'document' ? documentTypeOptions : [...folderTypeOptions, ...documentTypeOptions];
  const selectedType = allTypes.find((t) => String(t.id) === v.type_id);
  const isDocumentSelection = kind === 'document' || (selectedType ? !selectedType.is_container : false);

  const levelOptions = useOptions(() => recordLevelsApi.list(), 'record-levels-options');
  const statusOptions = useOptions(() => api.recordStatusesApi.list({ 'page.size': 200 } as never), 'record-statuses-options');
  const activityOptions = useOptions(() => activitiesApi.list({ 'page.size': 200 } as never), 'activities-options');
  const confidentialityOptions = useOptions(() => recordConfidentialitiesApi.list(), 'record-confidentialities-options');
  const userOptions = useOptions(() => usersApi.list({ 'page.size': 200 } as never), 'users-options');
  const { data: parentCandidatesData } = useQuery({ queryKey: ['records-parent-candidates'], queryFn: () => api.getRecords({ 'page.size': 200 }) });
  const parentOptions = ((parentCandidatesData?.data ?? []) as Entity[])
    .filter((rec) => (rec.type as Entity | undefined)?.is_container)
    .map((rec) => ({
      value: String(rec.id),
      label: `${String(rec.code ?? '')} — ${String(rec.name ?? '')}`,
      parent_id: rec.parent_id != null && rec.parent_id !== '' ? String(rec.parent_id) : null,
    }));

  function setField(name: string, value: string) {
    setV((p) => ({ ...p, [name]: value }));
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (isDocumentSelection && !v.parent_id) {
      window.alert('Un document doit être classé dans un dossier parent.');
      return;
    }
    const payload: Record<string, unknown> = { ...v };
    for (const k of Object.keys(payload)) {
      if (payload[k] === '') delete payload[k];
    }
    payload.requires_approval = v.requires_approval === '1';
    if (Object.keys(metadata).length > 0) payload.metadata = metadata;

    if (mode === 'edit' && id) await api.recordsApi.update(id, payload);
    else {
      const created = await create.mutateAsync(payload);
      router.push(`/records/${(created.data as Entity).id}`);
      return;
    }
    router.push(`/records/${id}`);
  }

  // Création dossier/document : tant qu'aucune typologie n'est choisie, on
  // n'affiche QUE la modale de choix (plein écran), pas le formulaire derrière.
  const needsTypePick = mode === 'create' && (kind === 'folder' || kind === 'document') && !lockedTypeId && !v.type_id;

  if (needsTypePick) {
    return (
      <>
        {typeModalReady && (
          <RecordUniversalModal
            kind={kind as 'folder' | 'document'}
            onClose={() => router.push('/records')}
            onSelect={(typeId) => { setField('type_id', typeId); setTypeModalOpen(false); }}
          />
        )}
      </>
    );
  }

  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4 pb-8">
      <header className="flex items-center justify-between">
        <h1 className="flex items-center gap-2 text-xl font-semibold">
          {mode === 'edit' ? 'Modifier la notice' : kind === 'folder' ? (<><Icon name="folderPlus" className="h-5 w-5 text-primary" /> Nouveau dossier</>) : kind === 'document' ? (<><Icon name="fileText" className="h-5 w-5 text-primary" /> Nouveau document</>) : 'Nouvelle notice'}
        </h1>
        <button type="button" onClick={() => router.push(mode === 'edit' && id ? `/records/${id}` : '/records')} className="rounded border border-border px-3 py-1.5 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Annuler</button>
      </header>

      <fieldset className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
        <legend className="px-1 text-sm font-semibold">Identification</legend>
        <Field label="Nom *" value={v.name} onChange={(x) => setField('name', x)} />
        <Field label="Code" value={v.code} onChange={(x) => setField('code', x)} help="Généré automatiquement depuis la typologie si vide." />
        <Field label="Description" value={v.description} onChange={(x) => setField('description', x)} />
        <div className="flex flex-col gap-1">
          <span className="text-sm">Typologie {kind ? `(${kind === 'folder' ? 'dossier' : 'document'})` : '(dossier ou document)'}</span>
          <div className="flex items-center gap-2">
            <button
              type="button"
              disabled={Boolean(lockedTypeId) || kind !== null}
              onClick={() => setTypeModalOpen(true)}
              className="flex flex-1 items-center justify-between gap-2 rounded border border-border bg-background px-2 py-1.5 text-sm disabled:bg-muted disabled:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              <span className={v.type_id ? '' : 'text-muted-foreground'}>
                {typeOptions.find((o) => o.value === v.type_id)?.label ?? '—'}
              </span>
              <Icon name="chevronDown" className="h-4 w-4 shrink-0 text-muted-foreground" />
            </button>
            {kind && (
              <button type="button" onClick={() => setTypeModalOpen(true)} className="shrink-0 rounded border border-border px-2 py-1.5 text-xs hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                Choisir…
              </button>
            )}
          </div>
          <p className="text-xs text-muted-foreground">{kind ? 'Typologie choisie via la fenêtre de sélection.' : undefined}</p>
        </div>
        <Field label="Niveau" value={v.level_id} onChange={(x) => setField('level_id', x)} options={levelOptions} />
        <Field label="Statut" value={v.status_id} onChange={(x) => setField('status_id', x)} options={statusOptions} />
        <Field label="Activité" value={v.activity_id} onChange={(x) => setField('activity_id', x)} options={activityOptions} />
        <Field
          label={isDocumentSelection ? 'Dossier parent *' : 'Dossier parent'}
          value={v.parent_id} onChange={(x) => setField('parent_id', x)} options={parentOptions}
          help={isDocumentSelection ? 'Un document doit être classé dans un dossier.' : undefined}
        />
        <Field label="Assignée à" value={v.assigned_to} onChange={(x) => setField('assigned_to', x)} options={userOptions} />
      </fieldset>

      <fieldset className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
        <legend className="px-1 text-sm font-semibold">Accès et confidentialité</legend>
        <Field label="Niveau d'accès" value={v.access_level} onChange={(x) => setField('access_level', x)}
          options={Object.entries(ACCESS_LEVEL_LABELS).map(([value, label]) => ({ value, label }))} />
        <Field label="Confidentialité" value={v.confidentiality_id} onChange={(x) => setField('confidentiality_id', x)} options={confidentialityOptions} />
        <Field label="Nécessite une approbation" value={v.requires_approval} onChange={(x) => setField('requires_approval', x)} type="checkbox" />
      </fieldset>

      <fieldset className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
        <legend className="px-1 text-sm font-semibold">Dates</legend>
        <Field label="Début" value={v.start_date} onChange={(x) => setField('start_date', x)} type="date" />
        <Field label="Fin" value={v.end_date} onChange={(x) => setField('end_date', x)} type="date" />
        <Field label="Date exacte" value={v.date_exact} onChange={(x) => setField('date_exact', x)} type="date" />
      </fieldset>

      <MetadataFieldsSection recordId={mode === 'edit' ? id : undefined} typeId={v.type_id || undefined} value={metadata} onChange={setMetadata} />

      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Enregistrer</button></footer>

      {typeModalOpen && kind && typeModalReady && (
        <RecordUniversalModal
          kind={kind as 'folder' | 'document'}
          onClose={() => setTypeModalOpen(false)}
          onSelect={(typeId) => { setField('type_id', typeId); setTypeModalOpen(false); }}
        />
      )}
    </form>
  );
}

/* ---------------------------------------------------------------------------
 * Fiche notice — libellés résolus, tous les champs de RecordResource, et
 * onglets enfants/conteneurs/pièces jointes/auteurs éditables (voir gap A.04).
 * ------------------------------------------------------------------------- */
export function RecordDetail({ id }: { id: string }) {
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({ queryKey: ['record', id], queryFn: () => api.getRecord(id) });
  if (isLoading) return <p className="text-sm text-muted-foreground">Chargement…</p>;
  const r = (data?.data ?? {}) as Entity;
  const attachments = ((r.attachments ?? []) as Entity[]).filter((a) => a.attachment);
  const awaitingApproval = Boolean(r.requires_approval) && !r.approved_by;

  async function approve() {
    try {
      await api.approveRecord(id);
      queryClient.invalidateQueries({ queryKey: ['record', id] });
    } catch (e) {
      window.alert(e instanceof Error ? e.message : "Échec de l'approbation.");
    }
  }

  return (
    <div className="flex flex-col gap-4">
      <header className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p className="font-mono text-xs text-muted-foreground">{String(r.code ?? '')}</p>
          <h1 className="text-xl font-semibold">{String(r.name ?? '')}</h1>
          <p className="mt-1 max-w-3xl text-sm text-muted-foreground">{String(r.description ?? '') || '—'}</p>
        </div>
        <div className="flex items-center gap-2">
          {awaitingApproval && (
            <>
              <span className="rounded bg-muted px-2 py-1 text-xs text-muted-foreground">En attente d&apos;approbation</span>
              <button type="button" onClick={approve} className="rounded border border-primary px-3 py-1.5 text-sm text-primary hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Approuver</button>
            </>
          )}
          <Link href={`/records/${id}/edit`} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Modifier</Link>
        </div>
      </header>

      <div className="grid grid-cols-1 gap-4 xl:grid-cols-5">
        {/* ==================== COLONNE GAUCHE : description / catégories ==================== */}
        <div className="flex max-h-[85vh] flex-col gap-4 overflow-y-auto pr-1 xl:col-span-2">
          <InfoSections record={r} />
          <TabsTab recordId={id} />
        </div>

        {/* ==================== COLONNE DROITE : visualiseur + miniatures ==================== */}
        <div className="xl:col-span-3">
          <RecordViewer recordId={id} attachments={attachments} />
        </div>
      </div>
    </div>
  );
}

/* ---------------------------------------------------------------------------
 * Colonne gauche — champs regroupés en catégories, valeurs cliquables qui
 * lancent une recherche filtrée sur /records.
 * ------------------------------------------------------------------------- */

const COMMUNICATION_STATUS_LABELS: Record<string, string> = {
  pending: 'Demande en cours',
  approved: 'Validée',
  rejected: 'Rejetée',
  in_consultation: 'En consultation',
  returned: 'Retournée',
};

/**
 * Section « Communication » de la fiche notice — en lecture seule, dérivée du
 * module Communication (D05) via `?include=communications`. Le prêt/consultation
 * n'est plus saisi sur la notice (les champs dédiés ont été retirés).
 */
function communicationSection(record: Entity): { title: string; icon: IconKey; rows: { label: string; value: string; filter?: [string, string] }[] } {
  const communications = (record.communications ?? []) as Entity[];
  const active = communications.filter((c) => c.status !== 'returned' && c.status !== 'rejected');

  if (active.length === 0) {
    return {
      title: 'Communication',
      icon: 'clock',
      rows: [{ label: 'Statut', value: communications.length === 0 ? 'Aucune communication' : 'Aucune en cours' }],
    };
  }

  return {
    title: 'Communication',
    icon: 'clock',
    rows: active.flatMap((c) => [
      { label: `${String(c.code ?? '')} — ${String(c.name ?? '')}`, value: COMMUNICATION_STATUS_LABELS[String(c.status ?? '')] ?? String(c.status ?? '') },
      ...(c.return_date ? [{ label: 'Retour prévu', value: new Date(String(c.return_date)).toLocaleDateString('fr-FR') }] : []),
      ...(c.return_effective ? [{ label: 'Retour effectif', value: new Date(String(c.return_effective)).toLocaleDateString('fr-FR') }] : []),
    ]),
  };
}

function InfoSections({ record }: { record: Entity }) {
  const sections: { title: string; icon: IconKey; rows: { label: string; value: string; filter?: [string, string] }[] }[] = [
    {
      title: 'Identification',
      icon: 'fileText',
      rows: [
        { label: 'Code', value: String(record.code ?? '—'), filter: ['code', String(record.code ?? '')] },
        { label: 'Nom', value: String(record.name ?? '—'), filter: ['name', String(record.name ?? '')] },
        { label: 'Typologie', value: labelOf(record.type, 'name'), filter: ['type_id', String(record.type_id ?? '')] },
        { label: 'Niveau', value: labelOf(record.level, 'name'), filter: ['level_id', String(record.level_id ?? '')] },
        { label: 'Notice parente', value: record.parent ? `${labelOf(record.parent, 'code')} — ${labelOf(record.parent, 'name')}` : '—' },
        { label: 'Version', value: `${String(record.version_number ?? '1')}${record.is_current_version ? ' (courante)' : ''}` },
      ],
    },
    {
      title: 'Classement',
      icon: 'listCheck',
      rows: [
        { label: 'Statut', value: labelOf(record.status, 'name'), filter: ['status_id', String(record.status_id ?? '')] },
        { label: 'Activité', value: labelOf(record.activity, 'name'), filter: ['activity_id', String(record.activity_id ?? '')] },
        { label: 'Organisation', value: labelOf(record.organisation, 'name') },
      ],
    },
    {
      title: 'Accès & confidentialité',
      icon: 'shieldLock',
      rows: [
        { label: "Niveau d'accès", value: ACCESS_LEVEL_LABELS[String(record.access_level ?? '')] ?? String(record.access_level ?? '—'), filter: ['access_level', String(record.access_level ?? '')] },
        { label: 'Confidentialité', value: labelOf(record.confidentiality, 'name'), filter: ['confidentiality_id', String(record.confidentiality_id ?? '')] },
        { label: "Limite d'accès", value: labelOf(record.accessLimit, 'name'), filter: ['access_limit_id', String(record.access_limit_id ?? '')] },
        { label: "Nécessite une approbation", value: record.requires_approval ? 'Oui' : 'Non' },
        { label: 'Approuvée par', value: labelOf(record.approver, 'name') },
      ],
    },
    {
      title: 'Dates',
      icon: 'calendar',
      rows: [
        { label: 'Début', value: record.start_date ? new Date(String(record.start_date)).toLocaleDateString('fr-FR') : '—' },
        { label: 'Fin', value: record.end_date ? new Date(String(record.end_date)).toLocaleDateString('fr-FR') : '—' },
        { label: 'Date exacte', value: record.date_exact ? new Date(String(record.date_exact)).toLocaleDateString('fr-FR') : '—' },
        { label: 'Créée le', value: record.created_at ? new Date(String(record.created_at)).toLocaleDateString('fr-FR') : '—' },
      ],
    },
    communicationSection(record),
    {
      title: 'Assignation',
      icon: 'person',
      rows: [
        { label: 'Assignée à', value: labelOf(record.assignedUser, 'name'), filter: ['assigned_to', String(record.assigned_to ?? '')] },
      ],
    },
  ];

  return (
    <div className="flex flex-col gap-3">
      {sections.map((sec) => {
        const visible = sec.rows.filter((row) => row.value && row.value !== '—');
        if (visible.length === 0) return null;
        return (
          <section key={sec.title} className="rounded bg-surface p-3">
            <h2 className="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              <Icon name={sec.icon} className="h-4 w-4" />
              {sec.title}
            </h2>
            <dl className="space-y-1.5">
              {sec.rows.map((row) => {
                if (!row.value || row.value === '—') return null;
                const clickable = row.filter && row.filter[1];
                return (
                  <div key={row.label} className="flex items-baseline justify-start gap-1.5 text-left text-sm">
                    <dt className="shrink-0 text-muted-foreground">{row.label} :</dt>
                    <dd className="min-w-0">
                      {clickable ? (
                        <Link href={`/records?filter[${row.filter![0]}]=${encodeURIComponent(row.filter![1])}`} className="font-medium hover:underline hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" title={`Rechercher : ${row.value}`}>
                          {row.value}
                        </Link>
                      ) : (
                        <span className="font-medium">{row.value}</span>
                      )}
                    </dd>
                  </div>
                );
              })}
            </dl>
          </section>
        );
      })}
    </div>
  );
}

/* ---------------------------------------------------------------------------
 * Onglets complémentaires (Sous-notices, Contenants, Pièces jointes, Auteurs)
 * ------------------------------------------------------------------------- */
function TabsTab({ recordId }: { recordId: string }) {
  const [tab, setTab] = useState<'children' | 'containers' | 'attachments' | 'authors'>('children');
  return (
    <div className="flex flex-col gap-2">
      <nav className="flex flex-wrap gap-1 text-sm">
        {([
          ['children', 'Sous-notices'],
          ['containers', 'Contenants'],
          ['attachments', 'Pièces jointes'],
          ['authors', 'Auteurs'],
        ] as const).map(([key, label]) => (
          <button
            key={key}
            type="button"
            onClick={() => setTab(key)}
            className={`rounded px-3 py-1.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${tab === key ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted'}`}
          >
            {label}
          </button>
        ))}
      </nav>
      {tab === 'children' && <ChildrenTab recordId={recordId} />}
      {tab === 'containers' && <ContainersTab recordId={recordId} />}
      {tab === 'attachments' && <AttachmentsTab recordId={recordId} />}
      {tab === 'authors' && <AuthorsTab recordId={recordId} />}
    </div>
  );
}

/* ---------------------------------------------------------------------------
 * Colonne droite — visualiseur du document (PDF, image, vidéo, audio, Office)
 * avec bandeau de miniatures des autres pièces jointes (carrousel horizontal).
 * ------------------------------------------------------------------------- */
function RecordViewer({ recordId, attachments }: { recordId: string; attachments: Entity[] }) {
  const [activeIndex, setActiveIndex] = useState(0);
  if (attachments.length === 0) {
    return (
      <div className="flex h-full min-h-[50vh] flex-col items-center justify-center gap-3 rounded bg-surface text-center">
        <Icon name="fileUp" className="h-14 w-14 text-muted-foreground/30" />
        <p className="text-sm text-muted-foreground">Aucune pièce jointe à visualiser.</p>
      </div>
    );
  }

  const active = attachments[Math.min(activeIndex, attachments.length - 1)]!;
  const attachment = (active.attachment ?? {}) as Entity;
  const fileId = attachment.id;
  const mime = String(attachment.mime_type ?? '');
  const name = String(attachment.name ?? 'Fichier');
  const ext = (String(attachment.file_extension ?? '').split('.')[0] ?? '').toLowerCase();
  const isOffice = /(wordprocessingml|spreadsheetml|presentationml|msword|ms-excel|ms-powerpoint|officedocument)/.test(mime);
  const kind: 'pdf' | 'image' | 'video' | 'audio' | 'office' | 'other' =
    mime.includes('pdf') ? 'pdf'
    : mime.startsWith('image/') ? 'image'
    : mime.startsWith('video/') ? 'video'
    : mime.startsWith('audio/') ? 'audio'
    : isOffice ? 'office'
    : 'other';

  const url = api.recordAttachmentPreviewUrl(recordId, fileId as string | number);
  const downloadUrl = api.recordAttachmentDownloadUrl(recordId, fileId as string | number);

  return (
    <div className="flex h-full flex-col gap-3">
      <div className="flex min-h-[55vh] flex-1 flex-col overflow-hidden rounded bg-surface">
        <header className="flex flex-wrap items-center justify-between gap-2 border-b border-border px-4 py-2.5">
          <div className="flex min-w-0 items-center gap-2">
            <FileTypeIcon kind={kind} />
            <p className="truncate text-sm font-medium">{name}</p>
          </div>
          <a href={downloadUrl} download className="flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-xs text-primary-foreground">
            <Icon name="download" className="h-3.5 w-3.5" />
            Télécharger
          </a>
        </header>
        <div className="flex flex-1 items-start justify-center overflow-auto bg-muted/40 p-4">
          {kind === 'pdf' && <iframe src={url} title={name} className="h-[62vh] w-full rounded bg-white" />}
          {kind === 'image' && <img src={url} alt={name} className="max-h-[62vh] max-w-full rounded bg-white object-contain" />}
          {kind === 'video' && (
            <video controls className="max-h-[62vh] w-full rounded bg-black" src={url}>
              Votre navigateur ne peut pas lire cette vidéo.
            </video>
          )}
          {kind === 'audio' && (
            <div className="flex w-full max-w-xl flex-col gap-2 rounded bg-white p-4">
              <audio controls className="w-full" src={url} />
              <p className="text-center text-sm text-muted-foreground">{name}</p>
            </div>
          )}
          {kind === 'office' && (
            <iframe src={`https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(url)}`} title={name} className="h-[62vh] w-full rounded bg-white" />
          )}
          {kind === 'other' && (
            <div className="flex h-[62vh] w-full flex-col items-center justify-center gap-3 rounded bg-white text-center">
              <FileTypeIcon kind="other" big />
              <p className="text-sm text-muted-foreground">Aperçu non disponible pour ce type de fichier.</p>
              <a href={downloadUrl} download className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Télécharger</a>
            </div>
          )}
        </div>
      </div>

      {/* Bandeau de miniatures : clic pour basculer, flèches pour faire défiler */}
      {attachments.length > 1 && (
        <ThumbnailStrip
          attachments={attachments}
          recordId={recordId}
          activeIndex={Math.min(activeIndex, attachments.length - 1)}
          onSelect={setActiveIndex}
        />
      )}
    </div>
  );
}

function FileTypeIcon({ kind, big }: { kind: 'pdf' | 'image' | 'video' | 'audio' | 'office' | 'other'; big?: boolean }) {
  const cls = big ? 'h-12 w-12' : 'h-4 w-4';
  const icon: IconKey =
    kind === 'pdf' ? 'fileText'
    : kind === 'image' ? 'fileDigit'
    : kind === 'video' ? 'fileUp'
    : kind === 'audio' ? 'fileUp'
    : kind === 'office' ? 'fileText'
    : 'fileText';
  const color =
    kind === 'pdf' ? 'text-red-500'
    : kind === 'image' ? 'text-blue-500'
    : kind === 'video' ? 'text-purple-500'
    : kind === 'audio' ? 'text-green-500'
    : kind === 'office' ? 'text-amber-500'
    : 'text-muted-foreground';
  return <Icon name={icon} className={`${cls} ${color} shrink-0`} />;
}

function ThumbnailStrip({ attachments, recordId, activeIndex, onSelect }: {
  attachments: Entity[];
  recordId: string;
  activeIndex: number;
  onSelect: (i: number) => void;
}) {
  const scrollRef = useRef<HTMLDivElement>(null);

  function scroll(dir: -1 | 1) {
    scrollRef.current?.scrollBy({ left: dir * 220, behavior: 'smooth' });
  }

  return (
    <div className="flex items-center gap-2">
      <button type="button" onClick={() => scroll(-1)} className="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-surface text-muted-foreground hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" title="Faire défiler">
        <Icon name="chevronLeft" className="h-4 w-4" />
      </button>
      <div ref={scrollRef} className="flex flex-1 gap-2 overflow-x-auto scroll-smooth py-1">
        {attachments.map((a, i) => {
          const att = (a.attachment ?? {}) as Entity;
          const isActive = i === activeIndex;
          const mime = String(att.mime_type ?? '');
          const kind: 'pdf' | 'image' | 'video' | 'audio' | 'office' | 'other' =
            mime.includes('pdf') ? 'pdf'
            : mime.startsWith('image/') ? 'image'
            : mime.startsWith('video/') ? 'video'
            : mime.startsWith('audio/') ? 'audio'
            : /(wordprocessingml|spreadsheetml|presentationml|officedocument)/.test(mime) ? 'office'
            : 'other';
          return (
            <button
              key={i}
              type="button"
              onClick={() => onSelect(i)}
              className={`flex h-16 w-20 shrink-0 flex-col items-center justify-center gap-1 rounded border px-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${isActive ? 'border-primary bg-primary/10' : 'border-border bg-surface hover:bg-muted'}`}
              title={String(att.name ?? '')}
            >
              {kind === 'image' ? (
                <img src={api.recordAttachmentPreviewUrl(recordId, att.id as string | number)} alt="" className="h-9 w-14 rounded object-cover" />
              ) : (
                <FileTypeIcon kind={kind} />
              )}
              <span className="w-full truncate text-center text-[10px] text-muted-foreground">{String(att.file_extension ?? '')}</span>
            </button>
          );
        })}
      </div>
      <button type="button" onClick={() => scroll(1)} className="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-surface text-muted-foreground hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" title="Faire défiler">
        <Icon name="chevronRight" className="h-4 w-4" />
      </button>
    </div>
  );
}

function ChildrenTab({ recordId }: { recordId: string }) {
  const router = useRouter();
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({ queryKey: ['record-children', recordId], queryFn: () => api.getRecordChildren(recordId) });
  const rows = (data?.data ?? []) as Entity[];
  const [name, setName] = useState('');
  const [pickerKind, setPickerKind] = useState<'folder' | 'document' | null>(null);

  async function add(e: React.FormEvent) {
    e.preventDefault();
    if (!name.trim()) return;
    await api.createRecordChild(recordId, { name });
    setName('');
    queryClient.invalidateQueries({ queryKey: ['record-children', recordId] });
  }

  async function remove(childId: string | number) {
    if (!window.confirm('Envoyer cette sous-notice à la corbeille ?')) return;
    await api.deleteRecordChild(recordId, childId);
    queryClient.invalidateQueries({ queryKey: ['record-children', recordId] });
  }

  return (
    <div>
      <div className="mb-2 flex items-center justify-between">
        <h2 className="text-sm font-semibold">Sous-notices</h2>
        <div className="flex gap-2">
          <button type="button" onClick={() => setPickerKind('folder')} className="flex items-center gap-1.5 rounded border border-primary px-2 py-1 text-xs text-primary hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            <Icon name="folderPlus" className="h-3.5 w-3.5" />
            Sous-dossier
          </button>
          <button type="button" onClick={() => setPickerKind('document')} className="flex items-center gap-1.5 rounded bg-primary px-2 py-1 text-xs text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            <Icon name="fileText" className="h-3.5 w-3.5" />
            Document
          </button>
        </div>
      </div>
      {pickerKind && (
        <RecordUniversalModal
          kind={pickerKind}
          onClose={() => setPickerKind(null)}
          onSelect={(typeId) => router.push(`/records/create?kind=${pickerKind}&type_id=${typeId}&parent_id=${recordId}`)}
        />
      )}
      <DataTable
        columns={[
          { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
          { key: 'name', label: 'Nom', render: (r) => <Link href={`/records/${r.id}`} className="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">{String(r.name ?? '')}</Link> },
        ]}
        rows={rows}
        loading={isLoading}
        emptyLabel="Aucune sous-notice."
        actions={(row) => <button type="button" onClick={() => remove(row.id as string | number)} className="rounded border border-border px-2 py-1 text-xs text-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Supprimer</button>}
      />
      <form onSubmit={add} className="mt-3 flex items-end gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-1 flex-col gap-1"><span>Nom de la sous-notice</span><input value={name} onChange={(e) => setName(e.target.value)} required className="rounded border border-border bg-background px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary" /></label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Ajouter</button>
      </form>
    </div>
  );
}

function ContainersTab({ recordId }: { recordId: string }) {
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({ queryKey: ['record-containers', recordId], queryFn: () => api.getRecordContainers(recordId) });
  const rows = (data?.data ?? []) as Entity[];
  const containerOptions = useOptions(() => containersApi.list({ 'page.size': 200 } as never), 'containers-options', 'code');
  const [containerId, setContainerId] = useState('');

  async function add(e: React.FormEvent) {
    e.preventDefault();
    if (!containerId) return;
    await api.attachRecordContainer(recordId, containerId);
    setContainerId('');
    queryClient.invalidateQueries({ queryKey: ['record-containers', recordId] });
  }

  async function remove(containerIdToRemove: string | number) {
    await api.detachRecordContainer(recordId, containerIdToRemove);
    queryClient.invalidateQueries({ queryKey: ['record-containers', recordId] });
  }

  return (
    <div>
      <h2 className="mb-2 text-sm font-semibold">Contenants</h2>
      <DataTable
        columns={[{ key: 'container', label: 'Contenant', render: (r) => labelOf(r.container, 'code') }, { key: 'description', label: 'Description' }]}
        rows={rows}
        loading={isLoading}
        emptyLabel="Aucun contenant."
        actions={(row) => <button type="button" onClick={() => remove(row.container_id as string | number)} className="rounded border border-border px-2 py-1 text-xs text-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Détacher</button>}
      />
      <form onSubmit={add} className="mt-3 flex items-end gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-1 flex-col gap-1">
          <span>Contenant existant</span>
          <select value={containerId} onChange={(e) => setContainerId(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary">
            <option value="">—</option>
            {containerOptions.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Attacher</button>
      </form>
    </div>
  );
}

function AttachmentsTab({ recordId }: { recordId: string }) {
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({ queryKey: ['record-attachments', recordId], queryFn: () => api.getRecordAttachments(recordId) });
  const rows = (data?.data ?? []) as Entity[];
  const [uploading, setUploading] = useState(false);

  async function upload(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;
    setUploading(true);
    try {
      await api.uploadRecordAttachment(recordId, file);
      queryClient.invalidateQueries({ queryKey: ['record-attachments', recordId] });
    } finally {
      setUploading(false);
      e.target.value = '';
    }
  }

  async function remove(attachmentId: string | number) {
    if (!window.confirm('Supprimer cette pièce jointe ?')) return;
    await api.deleteRecordAttachment(recordId, attachmentId);
    queryClient.invalidateQueries({ queryKey: ['record-attachments', recordId] });
  }

  return (
    <div>
      <h2 className="mb-2 text-sm font-semibold">Pièces jointes</h2>
      <DataTable
        columns={[
          { key: 'attachment', label: 'Nom', render: (r) => labelOf(r.attachment, 'name') },
          { key: 'size', label: 'Taille', render: (r) => { const size = (r.attachment as Entity | undefined)?.size; return size ? `${Math.round(Number(size) / 1024)} Ko` : '—'; } },
        ]}
        rows={rows}
        loading={isLoading}
        emptyLabel="Aucune pièce jointe."
        actions={(row) => <button type="button" onClick={() => remove(row.attachment_id as string | number)} className="rounded border border-border px-2 py-1 text-xs text-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Supprimer</button>}
      />
      <label className="mt-3 flex items-center gap-2 border-t border-border pt-3 text-sm">
        <span>Ajouter un fichier</span>
        <input type="file" onChange={upload} disabled={uploading} className="text-sm" />
        {uploading && <span className="text-xs text-muted-foreground">Envoi…</span>}
      </label>
    </div>
  );
}

function AuthorsTab({ recordId }: { recordId: string }) {
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({ queryKey: ['record-authors', recordId], queryFn: () => api.getRecordAuthors(recordId) });
  const rows = (data?.data ?? []) as Entity[];
  const authorOptions = useOptions(() => apiFetch<{ data?: Entity[] }>('/api/v1/authors?page.size=200'), 'authors-options');
  const [authorId, setAuthorId] = useState('');

  async function add(e: React.FormEvent) {
    e.preventDefault();
    if (!authorId) return;
    await api.attachRecordAuthor(recordId, authorId);
    setAuthorId('');
    queryClient.invalidateQueries({ queryKey: ['record-authors', recordId] });
  }

  async function remove(authorIdToRemove: string | number) {
    await api.detachRecordAuthor(recordId, authorIdToRemove);
    queryClient.invalidateQueries({ queryKey: ['record-authors', recordId] });
  }

  return (
    <div>
      <h2 className="mb-2 text-sm font-semibold">Auteurs</h2>
      <DataTable
        columns={[{ key: 'name', label: 'Nom' }]}
        rows={rows}
        loading={isLoading}
        emptyLabel="Aucun auteur."
        actions={(row) => <button type="button" onClick={() => remove(row.id as string | number)} className="rounded border border-border px-2 py-1 text-xs text-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Détacher</button>}
      />
      <form onSubmit={add} className="mt-3 flex items-end gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-1 flex-col gap-1">
          <span>Auteur existant</span>
          <select value={authorId} onChange={(e) => setAuthorId(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary">
            <option value="">—</option>
            {authorOptions.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Associer</button>
      </form>
    </div>
  );
}

/** Formulaire auteur complet (type, formes, durée de vie, lieux) + parent via modal. */
export function AuthorForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const { data } = useResource(api.authorsApi, 'authors', id);
  const create = useCreate(api.authorsApi, 'authors');
  const [v, setV] = useState<Record<string, string>>({});
  const [parentOpen, setParentOpen] = useState(false);

  const { data: typesData } = useQuery({ queryKey: ['author-types'], queryFn: () => apiFetch<{ data: Entity[] }>('/api/v1/author-types') });
  const typeOptions = (typesData?.data ?? []).map((t) => ({ value: String(t.id), label: String(t.name ?? '') }));
  const { data: parentsData } = useQuery({ queryKey: ['authors-parents'], queryFn: () => api.authorsApi.list({ 'page.size': 200 } as never) });
  const parents = ((parentsData?.data ?? []) as Entity[]).filter((a) => String(a.id) !== (id ?? ''));
  const parentLabel = String(parents.find((p) => String(p.id) === v.parent_id)?.name ?? '');

  if (mode === 'edit' && data?.data && Object.keys(v).length === 0) {
    const d = data.data;
    setV({
      name: String(d.name ?? ''), type_id: String(d.type_id ?? ''), parallel_name: String(d.parallel_name ?? ''),
      other_name: String(d.other_name ?? ''), lifespan: String(d.lifespan ?? ''), locations: String(d.locations ?? ''),
      parent_id: String(d.parent_id ?? ''),
    });
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    const payload: Record<string, unknown> = { ...v };
    for (const k of Object.keys(payload)) if (payload[k] === '') delete payload[k];
    if (mode === 'edit' && id) await api.authorsApi.update(id, payload);
    else await create.mutateAsync(payload);
    router.push('/records/authors');
  }

  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{mode === 'edit' ? 'Modifier : auteur' : 'Nouvel auteur'}</h1>
        <button type="button" onClick={() => router.push('/records/authors')} className="rounded border border-border px-3 py-1.5 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Annuler</button>
      </header>

      <fieldset className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
        <legend className="px-1 text-sm font-semibold">Identité</legend>
        <Field label="Nom *" value={v.name} onChange={(x) => setV((p) => ({ ...p, name: x }))} />
        <Field label="Type d'auteur" value={v.type_id} onChange={(x) => setV((p) => ({ ...p, type_id: x }))} options={typeOptions} />
        <Field label="Forme parallèle" value={v.parallel_name} onChange={(x) => setV((p) => ({ ...p, parallel_name: x }))} />
        <Field label="Autre forme" value={v.other_name} onChange={(x) => setV((p) => ({ ...p, other_name: x }))} />
        <Field label="Durée de vie" value={v.lifespan} onChange={(x) => setV((p) => ({ ...p, lifespan: x }))} help="ex. 1900-1965 ou 1890-1958" />
        <Field label="Lieux" value={v.locations} onChange={(x) => setV((p) => ({ ...p, locations: x }))} />
      </fieldset>

      <fieldset className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2">
        <legend className="px-1 text-sm font-semibold">Hiérarchie</legend>
        <div className="flex flex-col gap-1 text-sm">
          <span>Auteur parent</span>
          <div className="flex items-center gap-2">
            <input readOnly value={parentLabel} placeholder="Aucun parent" className="flex-1 rounded border border-border bg-background px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary" />
            <button type="button" onClick={() => setParentOpen(true)} className="rounded border border-border px-3 py-1.5 hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Choisir…</button>
            {v.parent_id && <button type="button" onClick={() => setV((p) => ({ ...p, parent_id: '' }))} className="text-xs text-danger hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Retirer</button>}
          </div>
          <p className="text-xs text-muted-foreground">Rattachez cet auteur à un auteur parent (hiérarchie des producteurs).</p>
        </div>
      </fieldset>

      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Enregistrer</button></footer>

      {parentOpen && (
        <UniversalModal<Entity>
          open
          onClose={() => setParentOpen(false)}
          title="Choisir l'auteur parent"
          display="table"
          columns={[{ key: 'name', label: 'Nom', render: (p) => <span>{String(p.name ?? '')}</span> }]}
          rows={parents}
          rowKey={(p) => String(p.id)}
          filter={(p, q) => String(p.name ?? '').toLowerCase().includes(q.toLowerCase())}
          onSelect={(sel) => {
            const picked = sel[0];
            if (picked) setV((prev) => ({ ...prev, parent_id: String(picked.id) }));
            setParentOpen(false);
          }}
          emptyLabel="Aucun auteur trouvé."
        />
      )}
    </form>
  );
}

/** Fiche auteur (identité + parent). */
export function AuthorDetail({ id }: { id: string }) {
  const { data, isLoading } = useResource(api.authorsApi, 'authors', id);
  if (isLoading) return <p className="text-sm text-muted-foreground">Chargement…</p>;
  const a = data?.data ?? {};
  return (
    <div className="flex flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{String(a.name ?? 'Auteur')}</h1>
        <Link href={`/records/authors/${id}/edit`} className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Modifier</Link>
      </header>
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        {([['Nom', a.name], ['Type', (a.author_type as { name?: string } | undefined)?.name ?? a.type_id], ['Forme parallèle', a.parallel_name], ['Autre forme', a.other_name], ['Durée de vie', a.lifespan], ['Lieux', a.locations], ['Parent', (a.parent as { name?: string } | undefined)?.name ?? '—']] as [string, unknown][]).map(([k, val]) => (
          <div key={k} className="rounded border border-border bg-surface p-3">
            <p className="text-xs font-semibold uppercase text-muted-foreground">{k}</p>
            <p className="mt-1 text-sm">{String(val ?? '—')}</p>
          </div>
        ))}
      </div>
    </div>
  );
}

const EXPORT_FIELDS: { key: string; label: string }[] = [
  { key: 'code', label: 'Code' }, { key: 'name', label: 'Nom' }, { key: 'type', label: 'Typologie' },
  { key: 'level', label: 'Niveau' }, { key: 'status', label: 'Statut' }, { key: 'activity', label: 'Activité' },
  { key: 'date_exact', label: 'Date exacte' }, { key: 'date_start', label: 'Date début' }, { key: 'date_end', label: 'Date fin' },
  { key: 'description', label: 'Description' }, { key: 'content', label: 'Contenu' },
  { key: 'archival_history', label: 'Historique archivistique' }, { key: 'biographical_history', label: 'Notice biographique' },
  { key: 'access_conditions', label: "Conditions d'accès" }, { key: 'note', label: 'Note' },
  { key: 'organisation', label: 'Organisation' }, { key: 'parent', label: 'Parent' },
  { key: 'version', label: 'Version' }, { key: 'created_at', label: 'Créée le' },
];

/** Export des notices : format + choix des champs (étape intermédiaire). */
export function RecordsExport() {
  const [format, setFormat] = useState('excel');
  const [fields, setFields] = useState<string[]>(['code', 'name', 'description', 'date_start', 'date_end']);
  const [ready, setReady] = useState(false);

  function toggle(field: string) {
    setFields((prev) => (prev.includes(field) ? prev.filter((f) => f !== field) : [...prev, field]));
  }

  const exportUrl = format === 'excel'
    ? `/api/proxy/api/v1/records/export?format=excel&fields=${fields.join(',')}`
    : `/api/proxy/api/v1/records/export?format=${format}`;

  return (
    <div className="flex h-full flex-col gap-4">
      <header>
        <h1 className="text-xl font-semibold">Exporter les notices</h1>
        <p className="mt-1 text-sm text-muted-foreground">Choisissez le format, puis les champs à inclure avant d'exporter.</p>
      </header>
      <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4">
        <label className="flex flex-col gap-1 text-sm">
          <span>Format d'export</span>
          <select
            value={format}
            onChange={(e) => { setFormat(e.target.value); setReady(false); }}
            className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
          >
            <option value="excel">Excel (.xlsx)</option>
            <option value="seda">SEDA 2.1 (.xml)</option>
            <option value="ead">EAD (.xml)</option>
          </select>
        </label>

        {format === 'excel' && !ready ? (
          <>
            <div>
              <p className="mb-2 text-sm font-medium">Champs à exporter</p>
              <div className="grid grid-cols-2 gap-x-4 gap-y-1 md:grid-cols-3">
                {EXPORT_FIELDS.map((f) => (
                  <label key={f.key} className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={fields.includes(f.key)} onChange={() => toggle(f.key)} />
                    {f.label}
                  </label>
                ))}
              </div>
            </div>
            <div>
              <button
                type="button"
                onClick={() => setReady(true)}
                disabled={fields.length === 0}
                className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                Continuer
              </button>
            </div>
          </>
        ) : (
          <>
            <a
              href={exportUrl}
              className="inline-flex w-fit items-center gap-2 rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90"
            >
              Exporter
            </a>
            {format === 'excel' && (
              <button type="button" onClick={() => setReady(false)} className="w-fit text-xs text-muted-foreground hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                ← Modifier les champs exportés ({fields.length} sélectionnés)
              </button>
            )}
            <p className="text-xs text-muted-foreground">
              {format === 'excel' ? `Export Excel avec ${fields.length} champ(s).` : 'Le format XML exporte la structure complète.'}
            </p>
          </>
        )}
      </div>
    </div>
  );
}

const IMPORT_FIELDS: { key: string; label: string; required?: boolean }[] = [
  { key: 'code', label: 'Code', required: true },
  { key: 'name', label: 'Nom', required: true },
  { key: 'description', label: 'Description' },
  { key: 'type_id', label: 'Typologie (id)' },
  { key: 'start_date', label: 'Date début' },
  { key: 'end_date', label: 'Date fin' },
];

/** Import des notices : format + fichier, puis choix des champs et valeurs par défaut. */
export function RecordsImport() {
  const [format, setFormat] = useState('excel');
  const [file, setFile] = useState<File | null>(null);
  const [step, setStep] = useState<'choose' | 'map'>('choose');
  const [selected, setSelected] = useState<string[]>(['code', 'name', 'description']);
  const [defaults, setDefaults] = useState<Record<string, string>>({});
  const [busy, setBusy] = useState(false);
  const [result, setResult] = useState<{ created: number; updated: number; errors: string[] } | null>(null);
  const [message, setMessage] = useState<string | null>(null);

  function toggle(field: string) {
    setSelected((prev) => (prev.includes(field) ? prev.filter((f) => f !== field) : [...prev, field]));
  }

  async function doImport(e: React.FormEvent) {
    e.preventDefault();
    if (!file) return;
    setBusy(true);
    setMessage(null);
    setResult(null);
    try {
      const fd = new FormData();
      fd.append('file', file);
      fd.append('fields', selected.join(','));
      fd.append('defaults', JSON.stringify(defaults));
      const res = await fetch('/api/proxy/api/v1/records/import', { method: 'POST', body: fd });
      const json = await res.json().catch(() => null);
      if (!res.ok) {
        setMessage(json?.message ?? "Erreur d'import.");
        return;
      }
      setResult(json?.data ?? null);
    } catch {
      setMessage("Erreur réseau lors de l'import.");
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="flex h-full flex-col gap-4">
      <header>
        <h1 className="text-xl font-semibold">Importer des notices</h1>
        <p className="mt-1 text-sm text-muted-foreground">Choisissez le format et le fichier, puis les champs et valeurs par défaut.</p>
      </header>

      {step === 'choose' ? (
        <form
          onSubmit={(e) => { e.preventDefault(); setStep('map'); }}
          className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4"
        >
          <label className="flex flex-col gap-1 text-sm">
            <span>Format</span>
            <select value={format} onChange={(e) => setFormat(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
              <option value="excel">Excel (.xlsx, .xls)</option>
              <option value="seda">SEDA 2.1 (.xml)</option>
              <option value="ead">EAD (.xml)</option>
            </select>
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span>Fichier</span>
            <input type="file" accept={format === 'excel' ? '.xlsx,.xls' : '.xml'} onChange={(e) => setFile(e.target.files?.[0] ?? null)} required className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
          </label>
          <div>
            <button type="submit" disabled={!file} className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              Suivant : champs et valeurs par défaut
            </button>
          </div>
          {format !== 'excel' && <p className="text-xs text-muted-foreground">Import Excel uniquement pour la sélection de champs.</p>}
        </form>
      ) : (
        <form onSubmit={doImport} className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4">
          <p className="text-sm">
            <strong>{file?.name}</strong> — choisissez les champs à importer et les valeurs par défaut des champs obligatoires.
          </p>
          <div>
            <p className="mb-2 text-sm font-medium">Champs à importer</p>
            <div className="grid grid-cols-2 gap-x-4 gap-y-1 md:grid-cols-3">
              {IMPORT_FIELDS.map((f) => (
                <label key={f.key} className="flex items-center gap-2 text-sm">
                  <input type="checkbox" checked={selected.includes(f.key)} onChange={() => toggle(f.key)} disabled={f.required} />
                  {f.label} {f.required && <span className="text-danger">*</span>}
                </label>
              ))}
            </div>
          </div>
          <div>
            <p className="mb-2 text-sm font-medium">Valeurs par défaut (si colonne absente ou vide)</p>
            <div className="grid grid-cols-1 gap-2 md:grid-cols-2">
              {IMPORT_FIELDS.map((f) => (
                <label key={f.key} className="flex flex-col gap-1 text-sm">
                  <span>{f.label} {f.required && <span className="text-danger">*</span>}</span>
                  <input value={defaults[f.key] ?? ''} onChange={(e) => setDefaults((p) => ({ ...p, [f.key]: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary" />
                </label>
              ))}
            </div>
          </div>
          <div className="flex items-center gap-3">
            <button type="button" onClick={() => setStep('choose')} className="rounded border border-border px-4 py-2 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">← Retour</button>
            <button type="submit" disabled={busy || selected.length === 0} className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              {busy ? 'Import en cours…' : 'Importer'}
            </button>
          </div>
          {message && <p className="text-sm text-danger">{message}</p>}
          {result && (
            <div className="rounded border border-border bg-background p-3 text-sm">
              <p className="font-medium">{result.created} notice(s) créée(s) · {result.updated} mise(s) à jour · {result.errors.length} erreur(s).</p>
              {result.errors.length > 0 && (
                <ul className="mt-2 list-disc pl-5 text-xs text-danger">
                  {result.errors.map((err, i) => <li key={i}>{err}</li>)}
                </ul>
              )}
            </div>
          )}
        </form>
      )}
    </div>
  );
}

export const routes: FeatureRoute[] = [
  { path: '/records', List: MesDocuments, Detail: RecordDetail, Form: RecordForm },
  { path: '/records/trash', List: RecordTrash },
  { path: '/records/authors', List: makeList(api.authorsApi, 'authors', [{ key: 'name', label: 'Nom' }, { key: 'type_id', label: 'Type' }, { key: 'lifespan', label: 'Durée de vie' }, { key: 'parent', label: 'Parent', render: (r) => String((r.parent as { name?: string } | undefined)?.name ?? '—') }], { title: 'Auteurs', create: '/records/authors/create', detail: '/records/authors' }), Detail: AuthorDetail, Form: AuthorForm },
  { path: '/records/author-contacts', List: makeList(api.authorContactsApi, 'author-contacts', [{ key: 'name', label: 'Nom' }, { key: 'email', label: 'Email' }], { title: 'Contacts d’auteurs', create: '/records/author-contacts/create', detail: '/records/author-contacts' }), Form: makeForm(api.authorContactsApi, 'author-contacts', { title: 'Contact d’auteur', back: '/records/author-contacts', fields: [{ name: 'name', label: 'Nom', required: true }, { name: 'email', label: 'Email' }] }) },

  { path: '/tools/record-types', aliases: ['/settings/record-types'], List: makeList(api.recordTypesApi, 'record-types', REF_COLS, { title: 'Typologies de notices', create: '/tools/record-types/create', detail: '/tools/record-types' }), Form: makeForm(api.recordTypesApi, 'record-types', { title: 'Typologie', back: '/tools/record-types', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/record-statuses', aliases: ['/settings/record-statuses'], List: makeList(api.recordStatusesApi, 'record-statuses', REF_COLS, { title: 'Statuts de notices', create: '/tools/record-statuses/create' }), Form: makeForm(api.recordStatusesApi, 'record-statuses', { title: 'Statut', back: '/tools/record-statuses', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/metadata-definitions', aliases: ['/settings/metadata-definitions'], List: makeList(api.metadataDefinitionsApi, 'metadata-definitions', [{ key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> }, { key: 'name', label: 'Nom' }, { key: 'data_type', label: 'Type' }], { title: 'Définitions de métadonnées', create: '/tools/metadata-definitions/create' }), Form: makeForm(api.metadataDefinitionsApi, 'metadata-definitions', { title: 'Définition de métadonnée', back: '/tools/metadata-definitions', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },

  { path: '/records/tree', List: RecordsTree },
  { path: '/records/drag-drop', List: DragDrop },
  { path: '/records/import', List: RecordsImport },
  { path: '/records/export', List: RecordsExport },
];
