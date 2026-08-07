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

/**
 * Les workplaces sont adressés par leur **code** (slug) : `/workplace/{code}`
 * (ex. `/workplace/rh`, `/workplace/sia2019`, `/workplace/dg-sg`). Le backend
 * résout aussi par id, ce qui conserve les anciens liens `/workplaces/{id}`.
 */
function workplacePath(code: unknown): string {
  const value = code != null && String(code).length > 0 ? String(code) : '';
  return `/workplace/${value}`;
}

const COLS: TableColumn<Entity>[] = [
  {
    key: 'name',
    label: 'Nom',
    render: (r) => (
      <Link href={workplacePath(r.code ?? r.id)} className="hover:underline">
        {String(r.name ?? '')}
      </Link>
    ),
  },
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
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
        actions={(row) => (
          <button
            type="button"
            onClick={() => {
              if (window.confirm('Supprimer ?')) destroy.mutate(row.id);
            }}
            className="rounded border border-border px-2 py-1 text-xs text-danger"
          >
            Supprimer
          </button>
        )} />
    </div>
  );
}

export function WorkplaceForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const { data } = useResource(api.workplacesApi, 'workplaces', id);
  const create = useCreate(api.workplacesApi, 'workplaces');
  const [v, setV] = useState<Record<string, string>>({});
  if (mode === 'edit' && data?.data && Object.keys(v).length === 0) {
    setV({
      code: String(data.data.code ?? ''),
      name: String(data.data.name ?? ''),
      description: String(data.data.description ?? ''),
    });
  }
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    let saved;
    if (mode === 'edit' && id) {
      saved = await api.workplacesApi.update(id, v);
    } else {
      saved = await create.mutateAsync(v);
    }
    const code = String((saved as { data?: { code?: string } })?.data?.code ?? v.code);
    router.push(`/workplace/${encodeURIComponent(code)}`);
  }
  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{mode === 'edit' ? 'Modifier — workplace' : 'Créer — workplace'}</h1>
        <button type="button" onClick={() => router.push('/workplaces')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
      </header>
      <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4">
        <Field
          label="Code * (adresse de l'espace)"
          hint="Ex. rh, sia2019, dg-sg — sert d'URL : /workplace/{code}"
          value={v.code}
          onChange={(x) => setV((p) => ({ ...p, code: x }))}
          required
        />
        <Field label="Nom *" value={v.name} onChange={(x) => setV((p) => ({ ...p, name: x }))} required />
        <Field label="Description" value={v.description} onChange={(x) => setV((p) => ({ ...p, description: x }))} />
      </div>
      <footer className="flex justify-end">
        <button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button>
      </footer>
    </form>
  );
}

function Field({
  label,
  value,
  onChange,
  required,
  hint,
}: {
  label: string;
  value?: string;
  onChange: (v: string) => void;
  required?: boolean;
  hint?: string;
}) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>{label} {required && <span className="text-danger">*</span>}</span>
      <input value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
      {hint && <span className="text-xs text-muted-foreground">{hint}</span>}
    </label>
  );
}

/**
 * Repli pour les anciens liens `/workplaces/{id}` : redirige vers le nouvel
 * emplacement `/workplace/{code}`. Le layout dédié et ses pages vivent dans
 * `src/app/(back-office)/workplace/[code]/`.
 */
export function WorkplaceDetail({ id }: { id: string }) {
  const router = useRouter();
  const { data } = useResource(api.workplacesApi, 'workplaces', id);
  const { data: resolved } = useQuery({
    queryKey: ['workplace-redirect', id],
    queryFn: async () => {
      const w = data?.data;
      if (w?.code) {
        router.replace(`/workplace/${encodeURIComponent(String(w.code))}`);
      }
      return w;
    },
    enabled: Boolean(data?.data?.code),
  });
  return <p className="text-sm text-muted-foreground">{resolved?.code ? 'Redirection…' : 'Chargement…'}</p>;
}

export const routes: FeatureRoute[] = [
  { path: '/workplaces', List: WorkplaceList, Detail: WorkplaceDetail, Form: WorkplaceForm },
];
