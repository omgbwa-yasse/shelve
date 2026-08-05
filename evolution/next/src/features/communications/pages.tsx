'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useResource, useCreate, useAction } from '@/lib/api/hooks';
import { DataTable } from '@/components/ui/table';
import * as api from './services/communication.service';
import type { FeatureRoute } from '@/lib/routing';

function makeList(r: ResourceApi, key: string, columns: TableColumn<Entity>[], o: { title: string; create?: string; actions?: (row: Entity) => React.ReactNode }) {
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
          actions={o.actions ?? ((row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>)} />
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
        <div className="grid max-w-2xl grid-cols-1 gap-4 rounded border border-border bg-surface p-4">
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

const COMM_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
  { key: 'status', label: 'Statut' },
];
const RESA_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
  { key: 'status', label: 'Statut' },
];

function CommActions({ id }: { id: string }) {
  const action = useAction(api.communicationsApi, 'communications');
  return (
    <div className="flex justify-end gap-1">
      {([['validate', 'Valider'], ['reject', 'Rejeter'], ['transmit', 'Transmettre']] as const).map(([verb, label]) => (
        <button key={verb} type="button" onClick={() => action.mutate({ id, verb })} className="rounded border border-border px-2 py-1 text-xs hover:bg-muted">{label}</button>
      ))}
    </div>
  );
}

export const routes: FeatureRoute[] = [
  { path: '/communications', List: makeList(api.communicationsApi, 'communications', COMM_COLS, { title: 'Communications', create: '/communications/create', actions: (row) => <CommActions id={String(row.id)} /> }), Form: makeForm(api.communicationsApi, 'communications', { title: 'Nouvelle communication', back: '/communications' }) },
  { path: '/communications/reservations', List: makeList(api.reservationsApi, 'reservations', RESA_COLS, { title: 'Réservations', create: '/communications/reservations/create' }), Form: makeForm(api.reservationsApi, 'reservations', { title: 'Nouvelle réservation', back: '/communications/reservations' }) },
];
