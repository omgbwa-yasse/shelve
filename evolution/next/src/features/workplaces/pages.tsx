'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useResource, useCreate } from '@/lib/api/hooks';
import { useQuery } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api/client';
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

/** Fiche détail d'un workplace avec onglets (membres, contenus, favoris). */
export function WorkplaceDetail({ id }: { id: string }) {
  const { data, isLoading } = useResource(api.workplacesApi, 'workplaces', id);
  const [tab, setTab] = useState<string>('infos');
  if (isLoading) return <p className="text-sm text-muted-foreground">Chargement…</p>;
  const w = data?.data ?? {};
  return (
    <div className="flex flex-col gap-4">
      <header className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold">{String(w.name ?? '')}</h1>
          <p className="text-sm text-muted-foreground">{String(w.description ?? '')}</p>
        </div>
        <Link href={`/workplaces/${id}/edit`} className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted">Modifier</Link>
      </header>
      <nav className="flex gap-1 border-b border-border text-sm">
        {(['infos', 'members', 'documents', 'folders', 'bookmarks'] as const).map((t) => (
          <button key={t} type="button" onClick={() => setTab(t)} className={`rounded-t border-b-2 px-3 py-1.5 ${tab === t ? 'border-primary' : 'border-transparent text-muted-foreground hover:bg-muted'}`}>
            {t === 'infos' ? 'Informations' : t === 'members' ? 'Membres' : t === 'documents' ? 'Documents' : t === 'folders' ? 'Dossiers' : 'Favoris'}
          </button>
        ))}
      </nav>
      {tab === 'infos' && (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
          {([['Nom', w.name], ['Description', w.description], ['Statut', w.status], ['Public', w.is_public ? 'Oui' : 'Non']] as [string, unknown][]).map(([k, v]) => (
            <div key={String(k)} className="rounded border border-border bg-surface p-3">
              <p className="text-xs font-semibold uppercase text-muted-foreground">{k}</p>
              <p className="mt-1 text-sm">{String(v ?? '—')}</p>
            </div>
          ))}
        </div>
      )}
      {tab === 'members' && <SubList title="Membres" apiPath={`/api/v1/workplaces/${id}/members`} cols={[{ key: 'user_id', label: 'Utilisateur' }, { key: 'role', label: 'Rôle' }]} />}
      {tab === 'documents' && <SubList title="Documents partagés" apiPath={`/api/v1/workplaces/${id}/content/documents`} cols={[{ key: 'name', label: 'Nom' }]} />}
      {tab === 'folders' && <SubList title="Dossiers partagés" apiPath={`/api/v1/workplaces/${id}/content/folders`} cols={[{ key: 'name', label: 'Nom' }]} />}
      {tab === 'bookmarks' && <SubList title="Favoris" apiPath={`/api/v1/workplaces/${id}/bookmarks`} cols={[{ key: 'bookmarkable_type', label: 'Type' }, { key: 'bookmarkable_id', label: 'Identifiant' }]} />}
    </div>
  );
}

function SubList({ title, apiPath, cols }: { title: string; apiPath: string; cols: TableColumn<Entity>[] }) {
  const { data, isLoading } = useQuery({
    queryKey: [apiPath],
    queryFn: async () => (await apiFetch<{ data?: Entity[] }>(`${apiPath}?page.size=50`)).data ?? [],
  });
  return (
    <div>
      <h2 className="mb-2 text-sm font-semibold">{title}</h2>
      <DataTable columns={cols} rows={data ?? []} loading={isLoading} emptyLabel="Aucun élément." />
    </div>
  );
}

export const routes: FeatureRoute[] = [
  { path: '/workplaces', List: WorkplaceList, Detail: WorkplaceDetail, Form: WorkplaceForm },
];
