'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useResource, useCreate, useAction } from '@/lib/api/hooks';
import { DataTable, Pagination } from '@/components/ui/table';
import * as api from './services/transferring.service';
import { SlipsImport, SlipsExport } from './components/slips-import-export';
import type { FeatureRoute } from '@/lib/routing';

function makeList(r: ResourceApi, key: string, columns: TableColumn<Entity>[], o: { title: string; create?: string; actions?: (row: Entity) => React.ReactNode }) {
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
          actions={o.actions ?? ((row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>)} />
        <Pagination page={page} totalPages={data?.meta?.last_page ?? 1} total={data?.meta?.total} onChange={setPage} />
      </div>
    );
  };
}

function makeForm(r: ResourceApi, key: string, o: { title: string; back: string }) {
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
        <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4">
          <Field label="Code" value={v.code} onChange={(x) => setV((p) => ({ ...p, code: x }))} />
          <Field label="Nom *" value={v.name} onChange={(x) => setV((p) => ({ ...p, name: x }))} required />
        </div>
        <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
      </form>
    );
  };
}

function Field({ label, value, onChange, required }: { label: string; value?: string; onChange: (v: string) => void; required?: boolean }) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>{label} {required && <span className="text-danger">*</span>}</span>
      <input value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
    </label>
  );
}

const SLIP_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
  { key: 'status', label: 'Statut' },
];
const REF_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
];

function SlipActions({ id }: { id: string }) {
  const action = useAction(api.slipsApi, 'slips');
  return (
    <div className="flex justify-end gap-1">
      {([['receive', 'Recevoir'], ['approve', 'Approuver']] as const).map(([verb, label]) => (
        <button key={verb} type="button" onClick={() => action.mutate({ id, verb })} className="rounded border border-border px-2 py-1 text-xs hover:bg-muted">{label}</button>
      ))}
    </div>
  );
}

export const routes: FeatureRoute[] = [
  { path: '/transferrings', List: makeList(api.slipsApi, 'slips', SLIP_COLS, { title: 'Bordereaux de versement', create: '/transferrings/create', actions: (row) => <SlipActions id={String(row.id)} /> }), Form: makeForm(api.slipsApi, 'slips', { title: 'Bordereau', back: '/transferrings' }) },
  { path: '/transferrings/declassement-lists', List: makeList(api.declassementListsApi, 'declassement-lists', SLIP_COLS, { title: 'Listes de déclassement', create: '/transferrings/declassement-lists/create' }), Form: makeForm(api.declassementListsApi, 'declassement-lists', { title: 'Liste de déclassement', back: '/transferrings/declassement-lists' }) },
  { path: '/transferrings/reactivations', List: makeList(api.declassementListsApi, 'reactivations', [{ key: 'record_id', label: 'Notice' }, { key: 'status', label: 'Statut' }], { title: 'Réactivations' }) },
  { path: '/tools/retentions', List: makeList(api.retentionsApi, 'retentions', REF_COLS, { title: 'Durées de conservation', create: '/tools/retentions/create' }), Form: makeForm(api.retentionsApi, 'retentions', { title: 'Règle de conservation', back: '/tools/retentions' }) },
  { path: '/settings/transferring-status', List: makeList(api.slipStatusesApi, 'slip-statuses', REF_COLS, { title: 'Statuts de transfert', create: '/settings/transferring-status/create' }), Form: makeForm(api.slipStatusesApi, 'slip-statuses', { title: 'Statut de transfert', back: '/settings/transferring-status' }) },
  { path: '/transferrings/import', List: SlipsImport },
  { path: '/transferrings/export', List: SlipsExport },
];
