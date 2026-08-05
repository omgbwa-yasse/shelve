'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useCreate } from '@/lib/api/hooks';
import { DataTable, Pagination } from '@/components/ui/table';
import * as api from './services/deposit.service';
import type { FeatureRoute } from '@/lib/routing';

function makeList(r: ResourceApi, key: string, columns: TableColumn<Entity>[], o: { title: string; create?: string }) {
  return function List() {
    const [page, setPage] = useState(1);
    const { data, isLoading, isError } = useResourceList(r, key, { page, 'page.size': 20 } as never);
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
        <Pagination page={page} totalPages={data?.meta?.last_page ?? 1} total={data?.meta?.total} onChange={setPage} />
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

const NAME_COLS: TableColumn<Entity>[] = [{ key: 'name', label: 'Nom' }];
const CODE_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
];

export const routes: FeatureRoute[] = [
  { path: '/deposits/buildings', List: makeList(api.buildingsApi, 'buildings', NAME_COLS, { title: 'Bâtiments', create: '/deposits/buildings/create' }), Form: makeForm(api.buildingsApi, 'buildings', { title: 'Bâtiment', back: '/deposits/buildings', fields: [{ name: 'name', label: 'Nom', required: true }] }) },
  { path: '/deposits/floors', List: makeList(api.floorsApi, 'floors', NAME_COLS, { title: 'Étages', create: '/deposits/floors/create' }), Form: makeForm(api.floorsApi, 'floors', { title: 'Étage', back: '/deposits/floors', fields: [{ name: 'name', label: 'Nom', required: true }] }) },
  { path: '/deposits/rooms', List: makeList(api.roomsApi, 'rooms', CODE_COLS, { title: 'Salles', create: '/deposits/rooms/create' }), Form: makeForm(api.roomsApi, 'rooms', { title: 'Salle', back: '/deposits/rooms', fields: [{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom', required: true }] }) },
  { path: '/deposits/shelves', List: makeList(api.shelvesApi, 'shelves', CODE_COLS, { title: 'Étagères', create: '/deposits/shelves/create' }), Form: makeForm(api.shelvesApi, 'shelves', { title: 'Étagère', back: '/deposits/shelves', fields: [{ name: 'code', label: 'Code', required: true }] }) },
  { path: '/deposits/containers', List: makeList(api.containersApi, 'containers', [{ key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> }, { key: 'capacity_cm', label: 'Capacité (cm)' }], { title: 'Contenants', create: '/deposits/containers/create' }), Form: makeForm(api.containersApi, 'containers', { title: 'Contenant', back: '/deposits/containers', fields: [{ name: 'code', label: 'Code', required: true }, { name: 'capacity_cm', label: 'Capacité (cm)' }] }) },
  { path: '/tools/container-status', aliases: ['/settings/container-status'], List: makeList(api.containerStatusesApi, 'container-statuses', NAME_COLS, { title: 'Statuts de contenants', create: '/tools/container-status/create' }), Form: makeForm(api.containerStatusesApi, 'container-statuses', { title: 'Statut de contenant', back: '/tools/container-status', fields: [{ name: 'name', label: 'Nom', required: true }] }) },
  { path: '/tools/container-property', aliases: ['/settings/container-property'], List: makeList(api.containerPropertiesApi, 'container-properties', NAME_COLS, { title: 'Propriétés de contenants', create: '/tools/container-property/create' }), Form: makeForm(api.containerPropertiesApi, 'container-properties', { title: 'Propriété de contenant', back: '/tools/container-property', fields: [{ name: 'name', label: 'Nom', required: true }] }) },
];
