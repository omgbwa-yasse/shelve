'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import type { Column, FilterSpec, ActionSpec, ResourceConfig } from '@/lib/crud/types';
import type { Entity, PaginatedEnvelope } from '@/lib/api/types';

/**
 * Écran LISTE universel — rendu depuis une `ResourceConfig` : filtres,
 * recherche, tri, pagination serveur, actions de ligne/de page, export.
 */
export function ListScreen({ config }: { config: ResourceConfig }) {
  const router = useRouter();
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [debounced, setDebounced] = useState('');
  const [filters, setFilters] = useState<Record<string, string>>({});

  const params: Record<string, unknown> = { page, per_page: 20 };
  if (debounced) params.search = debounced;
  for (const [k, v] of Object.entries(filters)) if (v) params[k] = v;

  const { data, isPending, isError, error } = useQuery({
    queryKey: [config.path, page, debounced, filters],
    queryFn: () => config.api.list(params as never) as Promise<PaginatedEnvelope<Entity>>,
  });

  const removeMutation = useMutation({
    mutationFn: (id: string | number) => config.api.destroy(id),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [config.path] }),
  });

  const rows = data?.data ?? [];
  const meta = data?.meta;
  const total = meta?.total ?? rows.length;

  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold">{config.plural}</h1>
          {config.description && <p className="mt-1 text-sm text-muted-foreground">{config.description}</p>}
        </div>
        <div className="flex gap-2">
          {config.exportable && (
            <a
              href={`${config.path}?export=1${debounced ? `&search=${encodeURIComponent(debounced)}` : ''}`}
              className="rounded border border-border bg-surface px-3 py-1.5 text-sm font-medium hover:bg-muted"
            >
              Exporter
            </a>
          )}
          {config.creatable !== false && (
            <Link
              href={`${config.path}/create`}
              className="rounded bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground hover:opacity-90"
            >
              + Nouveau
            </Link>
          )}
        </div>
      </header>

      <div className="flex flex-wrap items-center gap-2">
        <input
          type="search"
          placeholder={`Rechercher…`}
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            window.setTimeout(() => {
              setDebounced(e.target.value);
              setPage(1);
            }, 300);
          }}
          className="w-64 rounded border border-border bg-surface px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
        />
        {config.filters?.map((filter) => (
          <FilterControl key={filter.name} filter={filter} value={filters[filter.name]} onChange={(v) => { setFilters((p) => ({ ...p, [filter.name]: v })); setPage(1); }} />
        ))}
        {Object.values(filters).some(Boolean) && (
          <button type="button" onClick={() => { setFilters({}); }} className="rounded border border-border px-2 py-1.5 text-sm text-muted-foreground hover:bg-muted">
            Réinitialiser
          </button>
        )}
      </div>

      <div className="min-h-0 flex-1 overflow-auto rounded border border-border">
        <table className="w-full text-left text-sm">
          <thead className="sticky top-0 bg-surface">
            <tr>
              {(config.columns ?? []).map((col) => (
                <th key={col.key} className={`px-3 py-2 font-medium text-muted-foreground ${col.className ?? ''}`}>
                  {col.label}
                </th>
              ))}
              {config.rowActions && config.rowActions.length > 0 && <th className="px-3 py-2" />}
            </tr>
          </thead>
          <tbody>
            {isPending && (
              <tr><td colSpan={(config.columns?.length ?? 1) + 1} className="px-3 py-8 text-center text-muted-foreground">Chargement…</td></tr>
            )}
            {!isPending && isError && (
              <tr><td colSpan={(config.columns?.length ?? 1) + 1} className="px-3 py-8 text-center text-danger">Erreur de chargement</td></tr>
            )}
            {!isPending && rows.map((row) => (
              <Row key={String(row.id)} row={row} config={config} onDelete={(id) => removeMutation.mutate(id)} />
            ))}
            {!isPending && rows.length === 0 && (
              <tr><td colSpan={(config.columns?.length ?? 1) + 1} className="px-3 py-8 text-center text-muted-foreground">Aucun résultat.</td></tr>
            )}
          </tbody>
        </table>
      </div>

      {meta && meta.last_page > 1 && (
        <footer className="flex items-center justify-between text-sm">
          <span className="text-muted-foreground">{total} résultat(s)</span>
          <div className="flex gap-2">
            <button type="button" disabled={page <= 1} onClick={() => setPage((p) => p - 1)} className="rounded border border-border px-2 py-1 disabled:opacity-40">Précédent</button>
            <span className="px-2 py-1">Page {meta.current_page} / {meta.last_page}</span>
            <button type="button" disabled={page >= meta.last_page} onClick={() => setPage((p) => p + 1)} className="rounded border border-border px-2 py-1 disabled:opacity-40">Suivant</button>
          </div>
        </footer>
      )}
    </div>
  );
}

function Row({ row, config, onDelete }: { row: Entity; config: ResourceConfig; onDelete: (id: string | number) => void }) {
  const id = row.id;
  const code = config.codeKey ? String(row[config.codeKey] ?? '') : '';
  const title = config.titleKey ? String(row[config.titleKey] ?? '') : '';
  const linkable = config.linkable !== false && id !== undefined && id !== null;

  return (
    <tr className="border-t border-border/60 hover:bg-muted">
      {(config.columns ?? []).map((col) => {
        const value = col.accessor ? col.accessor(row) : row[col.key];
        return (
          <td key={col.key} className={`px-3 py-2 ${col.className ?? ''}`}>
            {col.render ? col.render(value, row) : renderCell(value)}
          </td>
        );
      })}
      {config.rowActions && config.rowActions.length > 0 && (
        <td className="px-3 py-2 text-right">
          <div className="flex justify-end gap-1">
            {linkable && (
              <Link href={`${config.path}/${id}`} className="rounded border border-border px-2 py-1 text-xs hover:bg-muted">Voir</Link>
            )}
            {config.rowActions.map((action) => (
              <RowAction key={action.label} action={action} row={row} onDelete={onDelete} />
            ))}
          </div>
        </td>
      )}
    </tr>
  );
}

function RowAction({ action, row, onDelete }: { action: ActionSpec; row: Entity; onDelete: (id: string | number) => void }) {
  const id = row.id;
  if (id === undefined || id === null) return null;

  if (action.method === 'navigate' && action.href) {
    return <Link href={action.href} className="rounded border border-border px-2 py-1 text-xs hover:bg-muted">{action.label}</Link>;
  }

  return (
    <button
      type="button"
      onClick={() => {
        if (action.confirm && !window.confirm(action.confirm)) return;
        if (action.method === 'destroy') onDelete(id);
      }}
      className={`rounded border border-border px-2 py-1 text-xs hover:bg-muted ${action.variant === 'danger' ? 'text-danger' : ''}`}
    >
      {action.label}
    </button>
  );
}

function FilterControl({ filter, value, onChange }: { filter: FilterSpec; value?: string; onChange: (v: string) => void }) {
  if (filter.type === 'select') {
    return (
      <select value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-surface px-2 py-1.5 text-sm">
        <option value="">{filter.label} — tous</option>
        {(filter.options ?? []).map((o) => (
          <option key={String(o.value)} value={String(o.value)}>{o.label}</option>
        ))}
      </select>
    );
  }
  if (filter.type === 'boolean') {
    return (
      <select value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-surface px-2 py-1.5 text-sm">
        <option value="">{filter.label} — tous</option>
        <option value="1">Oui</option>
        <option value="0">Non</option>
      </select>
    );
  }
  if (filter.type === 'date') {
    return (
      <input type="date" value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-surface px-2 py-1.5 text-sm" />
    );
  }
  return (
    <input type="text" placeholder={filter.label} value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="w-40 rounded border border-border bg-surface px-2 py-1.5 text-sm" />
  );
}

function renderCell(value: unknown): React.ReactNode {
  if (value === null || value === undefined || value === '') return <span className="text-muted-foreground/60">—</span>;
  if (typeof value === 'boolean') return value ? 'Oui' : 'Non';
  if (typeof value === 'object') return JSON.stringify(value);
  return String(value);
}
