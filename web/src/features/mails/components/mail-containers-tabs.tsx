'use client';

import { useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity, ListParams } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useCreate, useUpdate } from '@/lib/api/hooks';
import { DataTable, Pagination } from '@/components/ui/table';
import {
  containersApi,
  roomsApi,
  shelvesApi,
  buildingsApi,
  floorsApi,
  containerPropertiesApi,
} from '@/features/deposits/services/deposit.service';

/**
 * Page « Boîtes » du menu Mails (route /mails/containers) — depuis l'unification
 * 2026-08-10, les contenants de courrier (usage=mail) sont des `containers`
 * localisés salle/rayonnage. La page offre 4 onglets à CRUD complet : Contenants
 * (boîtes mail) / Rayonnages / Salles / Bâtiments. Chaque onglet gère UNIQUEMENT
 * son type de données (les contenants se créent dans l'onglet Contenants, etc.).
 */

type FieldDef = {
  name: string;
  label: string;
  type?: 'text' | 'number' | 'select';
  required?: boolean;
  /** Clé dans `options` de l'onglet — liste distante (select org-scopé). */
  options?: string;
  /** Choix statiques pour un select. */
  choices?: [string, string][];
  hint?: string;
};

type OptionSource = {
  api: ResourceApi;
  params?: ListParams;
  valueKey: string;
  labelKey: string;
};

const ACTIVE_TAB =
  'rounded bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary';
const INACTIVE_TAB =
  'rounded px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary';

const TABS = [
  { key: 'containers', label: 'Contenants' },
  { key: 'shelves', label: 'Rayonnages' },
  { key: 'rooms', label: 'Salles' },
  { key: 'buildings', label: 'Bâtiments' },
] as const;

export function MailContainersTabs() {
  const [tab, setTab] = useState<(typeof TABS)[number]['key']>('containers');

  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex flex-col gap-3">
        <div className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">Boîtes et localisation</h1>
          <p className="text-sm text-muted-foreground">
            Contenants de courrier (usage=mail), rayonnages, salles et bâtiments.
          </p>
        </div>
        <nav className="flex gap-2" aria-label="Onglets">
          {TABS.map((t) => (
            <button
              key={t.key}
              type="button"
              onClick={() => setTab(t.key)}
              className={tab === t.key ? ACTIVE_TAB : INACTIVE_TAB}
            >
              {t.label}
            </button>
          ))}
        </nav>
      </header>

      {tab === 'containers' && <ContainersTab />}
      {tab === 'shelves' && <ShelvesTab />}
      {tab === 'rooms' && <RoomsTab />}
      {tab === 'buildings' && <BuildingsTab />}
    </div>
  );
}

/* ------------------------------------------------------------------ */
/* Champ de formulaire : input, number, select statique ou distante.   */
/* Les hooks vivent ici (pas dans une boucle de la table).             */
/* ------------------------------------------------------------------ */
function FormField({
  field,
  value,
  onChange,
  source,
}: {
  field: FieldDef;
  value: string;
  onChange: (v: string) => void;
  source?: OptionSource;
}) {
  const { data } = useResourceList(
    source?.api as ResourceApi,
    'opt',
    (source?.params ?? { 'page.size': 200 }) as never,
    !!source,
  );
  const options = source
    ? ((data?.data ?? []) as Entity[]).map((row) => ({
        value: String((row as Record<string, unknown>)[source.valueKey] ?? ''),
        label: String((row as Record<string, unknown>)[source.labelKey] ?? ''),
      }))
    : [];

  const choices: [string, string][] = field.choices ?? [];
  const isSelect = !!source || choices.length > 0;

  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>
        {field.label} {field.required && <span className="text-danger">*</span>}
      </span>
      {isSelect ? (
        <select
          value={value}
          onChange={(e) => onChange(e.target.value)}
          className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
        >
          <option value="">—</option>
          {source
            ? options.map((o) => (
                <option key={o.value} value={o.value}>
                  {o.label}
                </option>
              ))
            : choices.map(([val, lab]) => (
                <option key={val} value={val}>
                  {lab}
                </option>
              ))}
        </select>
      ) : (
        <input
          type={field.type === 'number' ? 'number' : 'text'}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
        />
      )}
      {field.hint && <small className="text-xs text-muted-foreground">{field.hint}</small>}
    </label>
  );
}

/* ------------------------------------------------------------------ */
/* Onglet générique : liste + formulaire création/édition inline       */
/* ------------------------------------------------------------------ */
function EntityTab({
  api,
  queryKey,
  listParams,
  columns,
  fields,
  title,
  options = {},
}: {
  api: ResourceApi;
  queryKey: string;
  listParams: ListParams;
  columns: TableColumn<Entity>[];
  fields: FieldDef[];
  title: string;
  options?: Record<string, OptionSource>;
}) {
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [form, setForm] = useState<Record<string, string>>({});
  const [editingId, setEditingId] = useState<string | number | null>(null);
  const [open, setOpen] = useState(false);

  const { data, isLoading, isError } = useResourceList(api, queryKey, { ...listParams, page } as never);
  const create = useCreate(api, queryKey);
  const update = useUpdate(api, queryKey);
  const destroy = useDestroy(api, queryKey);
  const rows = (data?.data ?? []) as Entity[];

  function reset() {
    setForm({});
    setEditingId(null);
    setOpen(false);
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    const payload: Record<string, unknown> = {};
    for (const f of fields) {
      if (f.type === 'number') {
        payload[f.name] = form[f.name] !== undefined && form[f.name] !== '' ? Number(form[f.name]) : null;
      } else {
        payload[f.name] = form[f.name] ?? '';
      }
    }
    if (editingId !== null) {
      await update.mutateAsync({ id: editingId, payload });
    } else {
      await create.mutateAsync(payload);
    }
    queryClient.invalidateQueries({ queryKey: [queryKey] });
    reset();
  }

  function startEdit(row: Entity) {
    const v: Record<string, string> = {};
    for (const f of fields) v[f.name] = String((row as Record<string, unknown>)[f.name] ?? '');
    setForm(v);
    setEditingId(row.id ?? null);
    setOpen(true);
  }

  return (
    <div className="flex min-h-0 flex-1 flex-col gap-3">
      <header className="flex items-center justify-between">
        <h2 className="text-sm font-semibold uppercase tracking-wide text-muted-foreground">{title}</h2>
        <button
          type="button"
          onClick={() => {
            reset();
            setOpen(true);
          }}
          className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          + Nouveau
        </button>
      </header>

      {open && (
        <form onSubmit={submit} className="grid w-full grid-cols-1 gap-3 rounded border border-border bg-surface p-4 md:grid-cols-3">
          {fields.map((f) => (
            <FormField
              key={f.name}
              field={f}
              value={form[f.name] ?? ''}
              onChange={(v) => setForm((p) => ({ ...p, [f.name]: v }))}
              source={f.options ? options[f.options] : undefined}
            />
          ))}
          <div className="flex items-end gap-2 md:col-span-3">
            <button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              {editingId !== null ? 'Enregistrer' : 'Créer'}
            </button>
            <button type="button" onClick={reset} className="rounded border border-border px-4 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              Annuler
            </button>
          </div>
        </form>
      )}

      <DataTable
        columns={columns}
        rows={rows}
        loading={isLoading}
        error={isError}
        actions={(row) => (
          <div className="flex justify-end gap-2">
            <button
              type="button"
              onClick={() => startEdit(row)}
              className="rounded border border-border px-2 py-1 text-xs hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              Modifier
            </button>
            <button
              type="button"
              onClick={() => {
                if (window.confirm('Supprimer ?')) destroy.mutate(row.id);
              }}
              className="rounded border border-danger/40 bg-danger/10 px-2 py-1 text-xs text-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              Supprimer
            </button>
          </div>
        )}
      />
      <Pagination page={page} totalPages={data?.meta?.last_page ?? 1} total={data?.meta?.total} onChange={setPage} />
    </div>
  );
}

/* ------------------------------------------------------------------ */
/* Onglet Contenants (boîtes de courrier, usage=mail)                  */
/* ------------------------------------------------------------------ */
const CONTAINER_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
  { key: 'shelve_id', label: 'Rayonnage', render: (r) => String(r.shelve_id ?? '—') },
  { key: 'usage', label: 'Usage' },
];

function ContainersTab() {
  return (
    <EntityTab
      api={containersApi}
      queryKey="containers-mail"
      title="Contenants de courrier (boîtes)"
      listParams={{ 'filter[usage]': 'mail', 'page.size': 20 } as never}
      columns={CONTAINER_COLS}
      fields={[
        { name: 'code', label: 'Code', required: true, hint: "Le code de l'organisation est ajouté automatiquement (ex. DG-001)." },
        { name: 'name', label: 'Nom' },
        { name: 'shelve_id', label: 'Rayonnage', options: 'shelves' },
        { name: 'property_id', label: 'Propriété', options: 'properties' },
      ]}
      options={{
        shelves: { api: shelvesApi, valueKey: 'id', labelKey: 'code' },
        properties: { api: containerPropertiesApi, valueKey: 'id', labelKey: 'name' },
      }}
    />
  );
}

/* ------------------------------------------------------------------ */
/* Onglet Rayonnages                                                   */
/* ------------------------------------------------------------------ */
const SHELF_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'room_id', label: 'Salle', render: (r) => String(r.room_id ?? '—') },
  { key: 'observation', label: 'Observation' },
];

function ShelvesTab() {
  return (
    <EntityTab
      api={shelvesApi}
      queryKey="shelves"
      title="Rayonnages"
      listParams={{ 'page.size': 20 } as never}
      columns={SHELF_COLS}
      fields={[
        { name: 'code', label: 'Code', required: true },
        { name: 'room_id', label: 'Salle', required: true, options: 'rooms' },
        { name: 'observation', label: 'Observation' },
      ]}
      options={{ rooms: { api: roomsApi, valueKey: 'id', labelKey: 'code' } }}
    />
  );
}

/* ------------------------------------------------------------------ */
/* Onglet Salles                                                       */
/* ------------------------------------------------------------------ */
const ROOM_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
  { key: 'type', label: 'Type' },
];

function RoomsTab() {
  return (
    <EntityTab
      api={roomsApi}
      queryKey="rooms"
      title="Salles"
      listParams={{ 'page.size': 20 } as never}
      columns={ROOM_COLS}
      fields={[
        { name: 'code', label: 'Code', required: true },
        { name: 'name', label: 'Nom', required: true },
        { name: 'floor_id', label: 'Étage', required: true, options: 'floors' },
        { name: 'type', label: 'Type', type: 'select', choices: [['archives', 'Archives'], ['producer', 'Producteur']] },
        { name: 'visibility', label: 'Visibilité', type: 'select', choices: [['public', 'Public'], ['private', 'Privé'], ['inherit', 'Héritée']] },
      ]}
      options={{ floors: { api: floorsApi, valueKey: 'id', labelKey: 'name' } }}
    />
  );
}

/* ------------------------------------------------------------------ */
/* Onglet Bâtiments                                                    */
/* ------------------------------------------------------------------ */
const BUILDING_COLS: TableColumn<Entity>[] = [
  { key: 'name', label: 'Nom' },
  { key: 'visibility', label: 'Visibilité' },
];

function BuildingsTab() {
  return (
    <EntityTab
      api={buildingsApi}
      queryKey="buildings"
      title="Bâtiments"
      listParams={{ 'page.size': 20 } as never}
      columns={BUILDING_COLS}
      fields={[
        { name: 'name', label: 'Nom', required: true },
        { name: 'description', label: 'Description' },
        { name: 'visibility', label: 'Visibilité', type: 'select', choices: [['public', 'Public'], ['private', 'Privé'], ['inherit', 'Héritée']] },
      ]}
    />
  );
}
