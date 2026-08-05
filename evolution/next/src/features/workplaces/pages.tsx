'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useResource, useCreate } from '@/lib/api/hooks';
import { DataTable } from '@/components/ui/table';
import * as api from './services/workplace.service';
import type { FeatureRoute } from '@/lib/routing';

const COLS: TableColumn<Entity>[] = [
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/workplaces/${r.id}`} className="hover:underline">{String(r.name ?? '')}</Link> },
  { key: 'status', label: 'Statut' },
  { key: 'is_public', label: 'Public', render: (r) => (r.is_public ? 'Oui' : 'Non') },
];

export function WorkplaceList() {
  const { data, isLoading, isError } = useResourceList(api.workplacesApi, 'workplaces', { 'page.size': 50 } as never);
  const destroy = useDestroy(api.workplacesApi, 'workplaces');
  const rows = (data?.data ?? []) as Entity[];
  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Espaces de travail</h1>
        <Link href="/workplaces/create" className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link>
      </header>
      <DataTable columns={COLS} rows={rows} loading={isLoading} error={isError}
        actions={(row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>} />
    </div>
  );
}

export function WorkplaceForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const { data } = useResource(api.workplacesApi, 'workplaces', id);
  const create = useCreate(api.workplacesApi, 'workplaces');
  const [v, setV] = useState<Record<string, string>>({});
  if (mode === 'edit' && data?.data && Object.keys(v).length === 0) {
    setV({ name: String(data.data.name ?? ''), description: String(data.data.description ?? '') });
  }
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (mode === 'edit' && id) await api.workplacesApi.update(id, v);
    else await create.mutateAsync(v);
    router.push('/workplaces');
  }
  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{mode === 'edit' ? 'Modifier — workplace' : 'Créer — workplace'}</h1>
        <button type="button" onClick={() => router.push('/workplaces')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
      </header>
      <div className="grid max-w-2xl grid-cols-1 gap-4 rounded border border-border bg-surface p-4">
        <Field label="Nom *" value={v.name} onChange={(x) => setV((p) => ({ ...p, name: x }))} required />
        <Field label="Description" value={v.description} onChange={(x) => setV((p) => ({ ...p, description: x }))} />
      </div>
      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
    </form>
  );
}

function Field({ label, value, onChange, required }: { label: string; value?: string; onChange: (v: string) => void; required?: boolean }) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>{label} {required && <span className="text-danger">*</span>}</span>
      <input value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
    </label>
  );
}

export const routes: FeatureRoute[] = [
  { path: '/workplaces', List: WorkplaceList, Form: WorkplaceForm },
];
