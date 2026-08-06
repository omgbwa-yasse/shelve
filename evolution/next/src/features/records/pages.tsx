'use client';

import { useState } from 'react';
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
import { Modal } from '@/components/ui/Modal';
import { AlphabetBar } from '@/components/ui/SelectionModal';
import * as api from './services/record.service';
import { ACCESS_LEVEL_LABELS, DATE_FORMAT_LABELS } from './services/record.service';
import { RecordsTree, DragDrop } from './components/records-views';
import { recordLevelsApi, recordConfidentialitiesApi, MetadataFieldsSection } from './components/record-form-fields';
import { containersApi } from '@/features/deposits/services/deposit.service';
import { usersApi } from '@/features/settings/services/setting.service';
import { activitiesApi, organisationsApi } from '@/features/tools/services/tool.service';
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

function labelOf(v: unknown, field: string): string {
  const o = v as Entity | undefined | null;
  if (!o) return '—';
  return String(o[field] ?? o.name ?? o.code ?? '—');
}

const RECORD_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/records/${r.id}`} className="hover:underline">{String(r.name ?? '')}</Link> },
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
 * Dossiers récents, Documents récents, Corbeille) avec affichage en
 * Arborescence (défaut), Grille ou Liste.
 * ------------------------------------------------------------------------- */
type TabKey = 'organisations' | 'plan' | 'folders' | 'documents' | 'trash';
type ViewMode = 'tree' | 'grid' | 'list';

const MD_TABS: { key: TabKey; label: string }[] = [
  { key: 'organisations', label: 'Organisations' },
  { key: 'plan', label: 'Plan de classement' },
  { key: 'folders', label: 'Dossiers récents' },
  { key: 'documents', label: 'Documents récents' },
  { key: 'trash', label: 'Corbeille' },
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
              <button type="button" onClick={() => toggle(n.key)} className="w-4 text-center text-xs text-muted-foreground">{isClosed ? '▶' : '▼'}</button>
            ) : <span className="w-4" />}
            <span className="w-4 text-xs">{hasKids ? '🗀' : '🗎'}</span>
            {n.recordId ? <Link href={`/records/${n.recordId}`} className="hover:underline">{n.label}</Link> : <span>{n.label}</span>}
          </div>
          {hasKids && !isClosed && render(n.children, depth + 1)}
        </div>
      );
    });
  }
  return (
    <div className="min-h-0 flex-1 overflow-auto rounded border border-border bg-surface p-3 text-sm">
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
        <Link key={String(r.id)} href={`/records/${r.id}`} className="rounded border border-border bg-surface p-3 hover:border-primary">
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
      label: <><span className="text-muted-foreground">{prefix === 'o' ? '🏢' : '🗀'}</span> {String(g.name ?? g.code ?? g.id)} <span className="text-xs text-muted-foreground">({recs.length})</span></>,
      children: [...kids, ...recs],
    };
  };
  return (byParent.get(null) ?? []).map(node);
}

function MesDocuments() {
  const [tab, setTab] = useState<TabKey>('organisations');
  const [view, setView] = useState<ViewMode>('tree');

  const recordsQ = useQuery({
    queryKey: ['md-records'],
    queryFn: () => api.getRecords({ 'page.size': 300, include: 'type,level,status' }),
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

  const allRecords = (recordsQ.data?.data ?? []) as Entity[];
  const trash = (trashQ.data?.data ?? []) as Entity[];
  const orgs = (orgsQ.data?.data ?? []) as Entity[];
  const acts = (actsQ.data?.data ?? []) as Entity[];

  const folders = allRecords.filter((r) => Boolean(r.is_container)).sort((a, b) => String(b.created_at ?? '').localeCompare(String(a.created_at ?? '')));
  const documents = allRecords.filter((r) => !r.is_container).sort((a, b) => String(b.created_at ?? '').localeCompare(String(a.created_at ?? '')));

  function content(): React.ReactNode {
    switch (tab) {
      case 'organisations': {
        if (view === 'tree') return <TreeView nodes={buildGroupTree(orgs, allRecords, 'organisation_id', 'o')} />;
        return <GridView records={allRecords} />;
      }
      case 'plan': {
        if (view === 'tree') return <TreeView nodes={buildGroupTree(acts, allRecords, 'activity_id', 'a')} />;
        return <GridView records={allRecords} />;
      }
      case 'folders': {
        if (view === 'tree') return <TreeView nodes={buildRecordTree(folders)} />;
        return <GridView records={folders} />;
      }
      case 'documents': {
        if (view === 'tree') return <TreeView nodes={buildRecordTree(documents)} />;
        return <GridView records={documents} />;
      }
      case 'trash': {
        if (view === 'tree') return <TreeView nodes={buildRecordTree(trash)} />;
        return <GridView records={trash} />;
      }
    }
  }

  const loading = recordsQ.isLoading || trashQ.isLoading || orgsQ.isLoading || actsQ.isLoading;

  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-xl font-semibold">Mes documents</h1>
        <div className="flex items-center gap-1 rounded border border-border bg-surface p-1 text-sm">
          {(['tree', 'grid', 'list'] as ViewMode[]).map((m) => (
            <button
              key={m}
              type="button"
              onClick={() => setView(m)}
              className={clsx('rounded px-3 py-1', view === m ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted')}
            >
              {m === 'tree' ? '🗀 Arborescence' : m === 'grid' ? '▦ Grille' : '☰ Liste'}
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
            className={clsx('rounded border px-3 py-1.5 text-sm', tab === t.key ? 'border-primary bg-primary/10 text-primary' : 'border-border bg-surface text-muted-foreground hover:bg-muted')}
          >
            {t.label}
          </button>
        ))}
      </div>
      {loading ? (
        <p className="text-sm text-muted-foreground">Chargement…</p>
      ) : view === 'list' ? (
        <DataTable columns={RECORD_COLS} rows={tab === 'folders' ? folders : tab === 'documents' ? documents : tab === 'trash' ? trash : allRecords} loading={false} error={false} />
      ) : (
        content()
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
            <button type="button" onClick={() => restore(row.id as string | number)} className="rounded border border-border px-2 py-1 text-xs hover:bg-muted">Restaurer</button>
            <button type="button" onClick={() => forceDelete(row.id as string | number, String(row.name))} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer définitivement</button>
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
  options?: { value: string; label: string }[]; help?: string; disabled?: boolean;
}) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>{label}</span>
      {options ? (
        <select value={value ?? ''} onChange={(e) => onChange(e.target.value)} disabled={disabled} className="rounded border border-border bg-background px-2 py-1.5 text-sm disabled:bg-muted disabled:text-muted-foreground">
          <option value="">—</option>
          {options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
        </select>
      ) : type === 'checkbox' ? (
        <input type="checkbox" checked={value === '1' || value === 'true'} onChange={(e) => onChange(e.target.checked ? '1' : '0')} className="h-4 w-4" />
      ) : (
        <input type={type} value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
      )}
      {help && <span className="text-xs text-muted-foreground">{help}</span>}
    </label>
  );
}

/* ---------------------------------------------------------------------------
 * Modale de choix de typologie — s'ouvre au clic sur "Nouveau dossier"/"Nouveau
 * document", liste les RecordType filtrés par is_container, et redirige vers
 * le formulaire avec le type figé (kind + type_id en query params).
 * ------------------------------------------------------------------------- */
const TYPE_PICKER_PAGE_SIZE = 24;
const TYPE_PICKER_ALPHABET_THRESHOLD = 12;
const TYPE_PICKER_SEARCH_MIN_CHARS = 3;

function TypePickerModal({ kind, onClose, onSelect }: {
  kind: 'folder' | 'document'; onClose: () => void; onSelect: (typeId: string) => void;
}) {
  const [activeLetter, setActiveLetter] = useState<string | null>(null);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const { data, isLoading } = useQuery({
    queryKey: ['record-types-picker', kind],
    queryFn: () => api.recordTypesApi.list({ 'page.size': 200, 'filter[is_container][eq]': kind === 'folder' ? '1' : '0' } as never),
  });
  const types = (data?.data ?? []) as Entity[];
  const filtered = types
    .filter((t) => Boolean(t.is_container) === (kind === 'folder'))
    .sort((a, b) => String(a.name ?? '').localeCompare(String(b.name ?? '')));

  // La recherche ne filtre qu'à partir de TYPE_PICKER_SEARCH_MIN_CHARS
  // caractères saisis ; en dessous, le jeu complet (respectant le filtre
  // alphabet) reste affiché.
  const trimmedSearch = search.trim().toLowerCase();
  const searchActive = trimmedSearch.length >= TYPE_PICKER_SEARCH_MIN_CHARS;
  const bySearch = searchActive
    ? filtered.filter((t) => `${t.name ?? ''} ${t.description ?? ''}`.toLowerCase().includes(trimmedSearch))
    : filtered;

  // Le bandeau alphabétique ne doit exposer que les lettres ayant au moins un
  // résultat dans le jeu courant (après recherche) — les autres sont masquées.
  const availableLetters = new Set(bySearch.map((t) => String(t.name ?? '').charAt(0).toUpperCase()));
  const effectiveLetter = activeLetter && availableLetters.has(activeLetter) ? activeLetter : null;

  const byLetter = effectiveLetter
    ? bySearch.filter((t) => String(t.name ?? '').toUpperCase().startsWith(effectiveLetter))
    : bySearch;

  const totalPages = Math.max(1, Math.ceil(byLetter.length / TYPE_PICKER_PAGE_SIZE));
  const currentPage = Math.min(page, totalPages);
  const pageItems = byLetter.slice((currentPage - 1) * TYPE_PICKER_PAGE_SIZE, currentPage * TYPE_PICKER_PAGE_SIZE);

  const showAlphabet = filtered.length > TYPE_PICKER_ALPHABET_THRESHOLD;
  const showPagination = byLetter.length > TYPE_PICKER_PAGE_SIZE;
  // Modale plein écran (50px de marge) : plus d'espace horizontal disponible,
  // donc jusqu'à 4 colonnes pour ne pas laisser les cartes trop clairsemées.
  const columns = pageItems.length > 9 ? 4 : pageItems.length > 4 ? 3 : 2;

  return (
    <Modal
      open
      onClose={onClose}
      title={kind === 'folder' ? '📁 Choisir une typologie de dossier' : '📄 Choisir une typologie de document'}
      size="full"
      search={{
        value: search,
        onChange: (v) => { setSearch(v); setPage(1); },
        placeholder: 'Rechercher une typologie… (3 caractères min.)',
      }}
      footer={showPagination ? (
        <div className="flex items-center justify-center gap-2 text-sm">
          <button type="button" disabled={currentPage <= 1} onClick={() => setPage(currentPage - 1)} className="rounded border border-border px-2 py-1 disabled:opacity-50">Précédent</button>
          <span>Page {currentPage} / {totalPages}</span>
          <button type="button" disabled={currentPage >= totalPages} onClick={() => setPage(currentPage + 1)} className="rounded border border-border px-2 py-1 disabled:opacity-50">Suivant</button>
        </div>
      ) : undefined}
    >
      <div className="flex flex-col gap-3">
        {showAlphabet && (
          <AlphabetBar
            activeLetter={effectiveLetter}
            onSelectLetter={(letter) => { setActiveLetter(letter); setPage(1); }}
            availableLetters={availableLetters}
          />
        )}

        {isLoading && <p className="text-sm text-muted-foreground">Chargement…</p>}
        {!isLoading && pageItems.length === 0 && (
          <p className="text-sm text-muted-foreground">Aucune typologie {kind === 'folder' ? 'de dossier' : 'de document'} configurée.</p>
        )}
        {pageItems.length > 0 && (
          <div className={clsx('grid gap-3', {
            'grid-cols-1 sm:grid-cols-2': columns === 2,
            'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3': columns === 3,
            'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4': columns === 4,
          })}>
            {pageItems.map((t) => (
              <button
                key={String(t.id)}
                type="button"
                onClick={() => onSelect(String(t.id))}
                className="flex flex-col items-start rounded border border-border px-3 py-2 text-left text-sm hover:border-primary hover:bg-muted"
              >
                <span className="font-medium">{String(t.name ?? '')}</span>
                {t.description ? <span className="text-xs text-muted-foreground">{String(t.description)}</span> : null}
              </button>
            ))}
          </div>
        )}
      </div>
    </Modal>
  );
}

function useOptions(fetcher: () => Promise<{ data?: Entity[] }>, key: string, labelField = 'name') {
  const { data } = useQuery({ queryKey: [key], queryFn: fetcher });
  return (data?.data ?? []).map((o) => ({ value: String(o.id), label: String(o[labelField] ?? o.name ?? o.code ?? o.id) }));
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
  const [typeModalOpen, setTypeModalOpen] = useState<boolean>(
    mode === 'create' && (kind === 'folder' || kind === 'document') && !lockedTypeId,
  );

  if (mode === 'edit' && data?.data && !loaded) {
    const e = data.data;
    setV({
      name: String(e.name ?? ''), code: String(e.code ?? ''), description: String(e.description ?? ''),
      type_id: String(e.type_id ?? ''), level_id: String(e.level_id ?? ''), status_id: String(e.status_id ?? ''),
      activity_id: String(e.activity_id ?? ''), parent_id: String(e.parent_id ?? ''), assigned_to: String(e.assigned_to ?? ''),
      access_level: String(e.access_level ?? ''), requires_approval: e.requires_approval ? '1' : '0',
      confidentiality_id: String(e.confidentiality_id ?? ''), access_limit_id: String(e.access_limit_id ?? ''),
      start_date: String(e.start_date ?? ''), end_date: String(e.end_date ?? ''), date_exact: String(e.date_exact ?? ''),
      date_format: String(e.date_format ?? ''), opening_date: String(e.opening_date ?? ''), closing_date: String(e.closing_date ?? ''),
      loaned_to: String(e.loaned_to ?? ''), loaned_at: String(e.loaned_at ?? ''),
      loan_planned_return_at: String(e.loan_planned_return_at ?? ''), loan_actual_return_at: String(e.loan_actual_return_at ?? ''),
      modified_after_loan: e.modified_after_loan ? '1' : '0',
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
    .map((rec) => ({ value: String(rec.id), label: `${String(rec.code ?? '')} — ${String(rec.name ?? '')}` }));

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
    payload.modified_after_loan = v.modified_after_loan === '1';
    if (Object.keys(metadata).length > 0) payload.metadata = metadata;

    if (mode === 'edit' && id) await api.recordsApi.update(id, payload);
    else {
      const created = await create.mutateAsync(payload);
      router.push(`/records/${(created.data as Entity).id}`);
      return;
    }
    router.push(`/records/${id}`);
  }

  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4 pb-8">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">
          {mode === 'edit' ? 'Modifier la notice' : kind === 'folder' ? '📁 Nouveau dossier' : kind === 'document' ? '📄 Nouveau document' : 'Nouvelle notice'}
        </h1>
        <button type="button" onClick={() => router.push(mode === 'edit' && id ? `/records/${id}` : '/records')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
      </header>

      <fieldset className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
        <legend className="px-1 text-sm font-semibold">Identification</legend>
        <Field label="Nom *" value={v.name} onChange={(x) => setField('name', x)} />
        <Field label="Code" value={v.code} onChange={(x) => setField('code', x)} help="Généré automatiquement depuis la typologie si vide." />
        <Field label="Description" value={v.description} onChange={(x) => setField('description', x)} />
        <div className="flex flex-col gap-1">
          <span className="text-sm">Typologie {kind ? `(${kind === 'folder' ? 'dossier' : 'document'})` : '(dossier ou document)'}</span>
          <div className="flex items-center gap-2">
            <select
              value={v.type_id}
              onChange={(e) => setField('type_id', e.target.value)}
              disabled={Boolean(lockedTypeId) || kind !== null}
              className="flex-1 rounded border border-border bg-background px-2 py-1.5 text-sm"
            >
              <option value="">—</option>
              {typeOptions.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
            </select>
            {kind && (
              <button type="button" onClick={() => setTypeModalOpen(true)} className="shrink-0 rounded border border-border px-2 py-1.5 text-xs hover:bg-muted">
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
        <Field label="Précision de date" value={v.date_format} onChange={(x) => setField('date_format', x)}
          options={Object.entries(DATE_FORMAT_LABELS).map(([value, label]) => ({ value, label }))} />
        <Field label="Ouverture" value={v.opening_date} onChange={(x) => setField('opening_date', x)} type="date" />
        <Field label="Fermeture" value={v.closing_date} onChange={(x) => setField('closing_date', x)} type="date" />
      </fieldset>

      <fieldset className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
        <legend className="px-1 text-sm font-semibold">Prêt</legend>
        <Field label="Prêtée à" value={v.loaned_to} onChange={(x) => setField('loaned_to', x)} options={userOptions} />
        <Field label="Prêtée le" value={v.loaned_at} onChange={(x) => setField('loaned_at', x)} type="date" />
        <Field label="Retour prévu" value={v.loan_planned_return_at} onChange={(x) => setField('loan_planned_return_at', x)} type="date" />
        <Field label="Retour effectif" value={v.loan_actual_return_at} onChange={(x) => setField('loan_actual_return_at', x)} type="date" />
        <Field label="Modifiée après prêt" value={v.modified_after_loan} onChange={(x) => setField('modified_after_loan', x)} type="checkbox" />
      </fieldset>

      <MetadataFieldsSection recordId={mode === 'edit' ? id : undefined} typeId={v.type_id || undefined} value={metadata} onChange={setMetadata} />

      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>

      {typeModalOpen && kind && (
        <TypePickerModal
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
  const { data, isLoading } = useQuery({ queryKey: ['record', id], queryFn: () => api.getRecord(id) });
  const [tab, setTab] = useState<string>('infos');
  if (isLoading) return <p className="text-sm text-muted-foreground">Chargement…</p>;
  const r = data?.data ?? {};

  const infoRows: [string, string][] = [
    ['Code', String(r.code ?? '—')],
    ['Nom', String(r.name ?? '—')],
    ['Description', String(r.description ?? '—')],
    ['Typologie', labelOf(r.type, 'name')],
    ['Niveau', labelOf(r.level, 'name')],
    ['Statut', labelOf(r.status, 'name')],
    ['Activité', labelOf(r.activity, 'name')],
    ['Notice parente', r.parent ? `${labelOf(r.parent, 'code')} — ${labelOf(r.parent, 'name')}` : '—'],
    ['Assignée à', labelOf(r.assignedUser, 'name')],
    ["Niveau d'accès", ACCESS_LEVEL_LABELS[String(r.access_level ?? '')] ?? String(r.access_level ?? '—')],
    ['Confidentialité', labelOf(r.confidentiality, 'name')],
    ["Limite d'accès", labelOf(r.accessLimit, 'name')],
    ["Nécessite une approbation", r.requires_approval ? 'Oui' : 'Non'],
    ['Approuvée par', labelOf(r.approver, 'name')],
    ['Version', `${String(r.version_number ?? '1')}${r.is_current_version ? ' (courante)' : ''}`],
    ['Début', r.start_date ? new Date(String(r.start_date)).toLocaleDateString('fr-FR') : '—'],
    ['Fin', r.end_date ? new Date(String(r.end_date)).toLocaleDateString('fr-FR') : '—'],
    ['Ouverture', r.opening_date ? new Date(String(r.opening_date)).toLocaleDateString('fr-FR') : '—'],
    ['Fermeture', r.closing_date ? new Date(String(r.closing_date)).toLocaleDateString('fr-FR') : '—'],
    ['Prêtée à', r.loaned_to ? `#${String(r.loaned_to)}` : '—'],
    ['Retour prévu', r.loan_planned_return_at ? new Date(String(r.loan_planned_return_at)).toLocaleDateString('fr-FR') : '—'],
    ['Retour effectif', r.loan_actual_return_at ? new Date(String(r.loan_actual_return_at)).toLocaleDateString('fr-FR') : '—'],
    ['Créée le', r.created_at ? new Date(String(r.created_at)).toLocaleDateString('fr-FR') : '—'],
  ];

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
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
          {infoRows.map(([k, val]) => (
            <div key={k} className="rounded border border-border bg-surface p-3">
              <p className="text-xs font-semibold uppercase text-muted-foreground">{k}</p>
              <p className="mt-1 text-sm">{val}</p>
            </div>
          ))}
        </div>
      )}
      {tab === 'children' && <ChildrenTab recordId={id} />}
      {tab === 'containers' && <ContainersTab recordId={id} />}
      {tab === 'attachments' && <AttachmentsTab recordId={id} />}
      {tab === 'authors' && <AuthorsTab recordId={id} />}
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
          <button type="button" onClick={() => setPickerKind('folder')} className="rounded border border-primary px-2 py-1 text-xs text-primary hover:bg-muted">📁 Sous-dossier</button>
          <button type="button" onClick={() => setPickerKind('document')} className="rounded bg-primary px-2 py-1 text-xs text-primary-foreground">📄 Document</button>
        </div>
      </div>
      {pickerKind && (
        <TypePickerModal
          kind={pickerKind}
          onClose={() => setPickerKind(null)}
          onSelect={(typeId) => router.push(`/records/create?kind=${pickerKind}&type_id=${typeId}&parent_id=${recordId}`)}
        />
      )}
      <DataTable
        columns={[
          { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
          { key: 'name', label: 'Nom', render: (r) => <Link href={`/records/${r.id}`} className="hover:underline">{String(r.name ?? '')}</Link> },
        ]}
        rows={rows}
        loading={isLoading}
        emptyLabel="Aucune sous-notice."
        actions={(row) => <button type="button" onClick={() => remove(row.id as string | number)} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>}
      />
      <form onSubmit={add} className="mt-3 flex items-end gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-1 flex-col gap-1"><span>Nom de la sous-notice</span><input value={name} onChange={(e) => setName(e.target.value)} required className="rounded border border-border bg-background px-2 py-1.5" /></label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground">Ajouter</button>
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
        actions={(row) => <button type="button" onClick={() => remove(row.container_id as string | number)} className="rounded border border-border px-2 py-1 text-xs text-danger">Détacher</button>}
      />
      <form onSubmit={add} className="mt-3 flex items-end gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-1 flex-col gap-1">
          <span>Contenant existant</span>
          <select value={containerId} onChange={(e) => setContainerId(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5">
            <option value="">—</option>
            {containerOptions.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground">Attacher</button>
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
        actions={(row) => <button type="button" onClick={() => remove(row.attachment_id as string | number)} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>}
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
        actions={(row) => <button type="button" onClick={() => remove(row.id as string | number)} className="rounded border border-border px-2 py-1 text-xs text-danger">Détacher</button>}
      />
      <form onSubmit={add} className="mt-3 flex items-end gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-1 flex-col gap-1">
          <span>Auteur existant</span>
          <select value={authorId} onChange={(e) => setAuthorId(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5">
            <option value="">—</option>
            {authorOptions.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground">Associer</button>
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
  const [parentSearch, setParentSearch] = useState('');

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

  const filteredParents = parents.filter((p) => (parentSearch ? String(p.name ?? '').toLowerCase().includes(parentSearch.toLowerCase()) : true));

  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{mode === 'edit' ? 'Modifier — auteur' : 'Nouvel auteur'}</h1>
        <button type="button" onClick={() => router.push('/records/authors')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
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
            <input readOnly value={parentLabel} placeholder="Aucun parent" className="flex-1 rounded border border-border bg-background px-2 py-1.5" />
            <button type="button" onClick={() => setParentOpen(true)} className="rounded border border-border px-3 py-1.5 hover:bg-muted">Choisir…</button>
            {v.parent_id && <button type="button" onClick={() => setV((p) => ({ ...p, parent_id: '' }))} className="text-xs text-danger hover:underline">Retirer</button>}
          </div>
          <p className="text-xs text-muted-foreground">Rattachez cet auteur à un auteur parent (hiérarchie des producteurs).</p>
        </div>
      </fieldset>

      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>

      {parentOpen && (
        <Modal open onClose={() => setParentOpen(false)} title="Choisir l'auteur parent">
          <div className="flex flex-col gap-3">
            <input type="search" placeholder="Rechercher un auteur…" value={parentSearch} onChange={(e) => setParentSearch(e.target.value)} className="rounded border border-border bg-background px-3 py-2 text-sm" autoFocus />
            <ul className="max-h-72 overflow-y-auto rounded border border-border text-sm">
              {filteredParents.map((p) => (
                <li key={String(p.id)}>
                  <button
                    type="button"
                    onClick={() => { setV((prev) => ({ ...prev, parent_id: String(p.id) })); setParentOpen(false); }}
                    className="w-full px-3 py-2 text-left hover:bg-muted"
                  >
                    {String(p.name ?? '')}
                  </button>
                </li>
              ))}
              {filteredParents.length === 0 && <li className="px-3 py-3 text-muted-foreground">Aucun auteur trouvé.</li>}
            </ul>
          </div>
        </Modal>
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
        <Link href={`/records/authors/${id}/edit`} className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted">Modifier</Link>
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
      <div className="grid max-w-2xl grid-cols-1 gap-4 rounded border border-border bg-surface p-4">
        <label className="flex flex-col gap-1 text-sm">
          <span>Format d'export</span>
          <select
            value={format}
            onChange={(e) => { setFormat(e.target.value); setReady(false); }}
            className="rounded border border-border bg-background px-2 py-1.5 text-sm"
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
                className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50"
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
              <button type="button" onClick={() => setReady(false)} className="w-fit text-xs text-muted-foreground hover:underline">
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
          className="grid max-w-2xl grid-cols-1 gap-4 rounded border border-border bg-surface p-4"
        >
          <label className="flex flex-col gap-1 text-sm">
            <span>Format</span>
            <select value={format} onChange={(e) => setFormat(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm">
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
            <button type="submit" disabled={!file} className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50">
              Suivant : champs et valeurs par défaut
            </button>
          </div>
          {format !== 'excel' && <p className="text-xs text-muted-foreground">Import Excel uniquement pour la sélection de champs.</p>}
        </form>
      ) : (
        <form onSubmit={doImport} className="grid max-w-2xl grid-cols-1 gap-4 rounded border border-border bg-surface p-4">
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
                  <input value={defaults[f.key] ?? ''} onChange={(e) => setDefaults((p) => ({ ...p, [f.key]: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5" />
                </label>
              ))}
            </div>
          </div>
          <div className="flex items-center gap-3">
            <button type="button" onClick={() => setStep('choose')} className="rounded border border-border px-4 py-2 text-sm hover:bg-muted">← Retour</button>
            <button type="submit" disabled={busy || selected.length === 0} className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50">
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
  { path: '/tools/record-supports', aliases: ['/settings/record-supports'], List: makeList(api.recordSupportsApi, 'record-supports', REF_COLS, { title: 'Supports', create: '/tools/record-supports/create' }), Form: makeForm(api.recordSupportsApi, 'record-supports', { title: 'Support', back: '/tools/record-supports', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/metadata-definitions', aliases: ['/settings/metadata-definitions'], List: makeList(api.metadataDefinitionsApi, 'metadata-definitions', [{ key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> }, { key: 'name', label: 'Nom' }, { key: 'data_type', label: 'Type' }], { title: 'Définitions de métadonnées', create: '/tools/metadata-definitions/create' }), Form: makeForm(api.metadataDefinitionsApi, 'metadata-definitions', { title: 'Définition de métadonnée', back: '/tools/metadata-definitions', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/folder-types', aliases: ['/settings/folder-types'], List: makeList(api.recordTypesApi, 'folder-types', REF_COLS, { title: 'Types de dossiers numériques', create: '/tools/folder-types/create' }), Form: makeForm(api.recordTypesApi, 'folder-types', { title: 'Type de dossier', back: '/tools/folder-types', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/document-types', aliases: ['/settings/document-types'], List: makeList(api.recordTypesApi, 'document-types', REF_COLS, { title: 'Types de documents numériques', create: '/tools/document-types/create' }), Form: makeForm(api.recordTypesApi, 'document-types', { title: 'Type de document', back: '/tools/document-types', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },

  { path: '/records/tree', List: RecordsTree },
  { path: '/records/drag-drop', List: DragDrop },
  { path: '/records/import', List: RecordsImport },
  { path: '/records/export', List: RecordsExport },
];
