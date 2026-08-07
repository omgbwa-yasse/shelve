'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useCreate } from '@/lib/api/hooks';
import { DataTable } from '@/components/ui/table';
import * as api from './services/dolly.service';
import type { FeatureRoute } from '@/lib/routing';

const COLS: TableColumn<Entity>[] = [
  { key: 'name', label: 'Nom' },
  { key: 'category', label: 'Catégorie' },
  { key: 'created_at', label: 'Créé le', render: (r) => (r.created_at ? new Date(String(r.created_at)).toLocaleDateString('fr-FR') : '—') },
];

export function DollyList() {
  const { data, isLoading, isError } = useResourceList(api.dolliesApi, 'dollies', { 'page.size': 50 } as never);
  const destroy = useDestroy(api.dolliesApi, 'dollies');
  const rows = (data?.data ?? []) as Entity[];
  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Chariots</h1>
        <Link href="/dollies/create" className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link>
      </header>
      <DataTable columns={COLS} rows={rows} loading={isLoading} error={isError}
        actions={(row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>} />
    </div>
  );
}

export function DollyForm() {
  const router = useRouter();
  const create = useCreate(api.dolliesApi, 'dollies');
  const [v, setV] = useState<Record<string, string>>({});
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    await create.mutateAsync(v);
    router.push('/dollies');
  }
  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Nouveau chariot</h1>
        <button type="button" onClick={() => router.push('/dollies')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
      </header>
      <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4">
        <label className="flex flex-col gap-1 text-sm">
          <span>Nom *</span>
          <input value={v.name ?? ''} onChange={(e) => setV((p) => ({ ...p, name: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
        </label>
      </div>
      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
    </form>
  );
}

export const routes: FeatureRoute[] = [
  { path: '/dollies', List: DollyList, Form: DollyForm },
];
