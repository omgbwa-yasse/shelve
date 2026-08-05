'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useResource, useCreate, useAction } from '@/lib/api/hooks';
import { DataTable, Pagination } from '@/components/ui/table';
import { PageHeader } from '@/components/ui/page';
import * as api from './services/workflow.service';
import { WorkflowDashboard } from './components/workflow-dashboard';
import type { FeatureRoute } from '@/lib/routing';

function makeList(r: ResourceApi, key: string, columns: TableColumn<Entity>[], o: { title: string; create?: string; del?: boolean; actions?: (row: Entity) => React.ReactNode }) {
  return function List() {
    const [page, setPage] = useState(1);
    const { data, isLoading, isError } = useResourceList(r, key, { page, 'page.size': 20 } as never);
    const destroy = useDestroy(r, key);
    const rows = (data?.data ?? []) as Entity[];
    const m = data?.meta;
    return (
      <div className="flex h-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{o.title}</h1>
          {o.create && <Link href={o.create} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link>}
        </header>
        <DataTable columns={columns} rows={rows} loading={isLoading} error={isError}
          actions={o.actions ?? (o.del ? (row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button> : undefined)} />
        <Pagination page={page} totalPages={m?.last_page ?? 1} total={m?.total} onChange={setPage} />
      </div>
    );
  };
}

const DEF_COLS: TableColumn<Entity>[] = [
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/workflow/definitions/${r.id}`} className="hover:underline">{String(r.name ?? '')}</Link> },
  { key: 'version', label: 'Version' },
  { key: 'status', label: 'Statut' },
];
const INST_COLS: TableColumn<Entity>[] = [
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/workflow/instances/${r.id}`} className="hover:underline">{String(r.name ?? '')}</Link> },
  { key: 'status', label: 'Statut' },
  { key: 'started_at', label: 'Démarré le', render: (r) => (r.started_at ? new Date(String(r.started_at)).toLocaleDateString('fr-FR') : '—') },
];
const TASK_COLS: TableColumn<Entity>[] = [
  { key: 'title', label: 'Titre', render: (r) => <Link href={`/workflow/tasks/${r.id}`} className="hover:underline">{String(r.title ?? '')}</Link> },
  { key: 'status', label: 'Statut' },
  { key: 'priority', label: 'Priorité' },
  { key: 'due_date', label: 'Échéance', render: (r) => (r.due_date ? new Date(String(r.due_date)).toLocaleDateString('fr-FR') : '—') },
];

function DefinitionForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const { data } = useResource(api.workflowDefinitionsApi, 'workflow-definitions', id);
  const create = useCreate(api.workflowDefinitionsApi, 'workflow-definitions');
  const [v, setV] = useState<Record<string, string>>({});
  if (mode === 'edit' && data?.data && Object.keys(v).length === 0) {
    setV({ name: String(data.data.name ?? ''), description: String(data.data.description ?? ''), bpmn_xml: String(data.data.bpmn_xml ?? '') });
  }
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (mode === 'edit' && id) await api.workflowDefinitionsApi.update(id, v);
    else await create.mutateAsync(v);
    router.push('/workflow/definitions');
  }
  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{mode === 'edit' ? 'Modifier — définition' : 'Créer — définition de workflow'}</h1>
        <button type="button" onClick={() => router.push('/workflow/definitions')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
      </header>
      <div className="grid max-w-3xl grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2">
        <Field label="Nom *" value={v.name} onChange={(x) => setV((p) => ({ ...p, name: x }))} required />
        <Field label="Description" value={v.description} onChange={(x) => setV((p) => ({ ...p, description: x }))} />
        <Field label="BPMN XML" value={v.bpmn_xml} onChange={(x) => setV((p) => ({ ...p, bpmn_xml: x }))} full />
      </div>
      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
    </form>
  );
}

function InstanceActions({ id }: { id: string }) {
  const action = useAction(api.workflowInstancesApi, 'workflow-instances');
  const verbs = [['start', 'Démarrer'], ['pause', 'Pause'], ['resume', 'Reprendre'], ['cancel', 'Annuler']] as const;
  return (
    <div className="flex gap-2">
      {verbs.map(([verb, label]) => (
        <button key={verb} type="button" onClick={() => action.mutate({ id, verb })} className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted">{label}</button>
      ))}
    </div>
  );
}

function Field({ label, value, onChange, required, full }: { label: string; value?: string; onChange: (v: string) => void; required?: boolean; full?: boolean }) {
  return (
    <label className={`flex flex-col gap-1 text-sm ${full ? 'md:col-span-2' : ''}`}>
      <span>{label} {required && <span className="text-danger">*</span>}</span>
      <input value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
    </label>
  );
}

export const routes: FeatureRoute[] = [
  { path: '/workflow/definitions', List: makeList(api.workflowDefinitionsApi, 'workflow-definitions', DEF_COLS, { title: 'Définitions de workflow', create: '/workflow/definitions/create', del: true }), Form: DefinitionForm },
  { path: '/workflow/instances', List: makeList(api.workflowInstancesApi, 'workflow-instances', INST_COLS, { title: 'Instances de workflow', create: '/workflow/instances/create', actions: (row) => <InstanceActions id={String(row.id)} /> }) },
  { path: '/workflow/tasks', List: makeList(api.tasksApi, 'tasks', TASK_COLS, { title: 'Tâches', create: '/workflow/tasks/create', del: true }) },
  { path: '/workflow/dashboard', List: WorkflowDashboard },
];
