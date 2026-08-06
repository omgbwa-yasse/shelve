'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api/client';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useResource, useDestroy, useCreate, useUpdate } from '@/lib/api/hooks';
import { DataTable, Pagination } from '@/components/ui/table';
import { PageHeader, InfoPanel, StatCard } from '@/components/ui/page';
import type { FeatureRoute } from '@/lib/routing';
import * as api from './services/project.service';
import { ATTACHABLE_LABELS, RESOURCE_TYPE_LABELS, type AttachableAlias } from './services/project.service';
import { GlobalMilestonesList, GlobalDeliverablesList, GlobalAlertsList, GlobalResourcesList, KanbanBoard, GanttChart } from './components/overview';
import { workplacesApi } from '@/features/workplaces/services/workplace.service';
import { organisationsApi } from '@/features/tools/services/tool.service';
import { usersApi } from '@/features/settings/services/setting.service';

/** Charge les options d'un périmètre (workplace / organisation / user) pour un champ clair. */
function loadAttachableOptions(type: AttachableAlias) {
  const params = { 'page.size': 200 } as never;
  const map = (r: { data?: Entity[] }) => (r.data ?? []).map((x) => ({ id: String(x.id), name: String(x.name ?? '') }));

  if (type === 'workplace') return workplacesApi.list(params).then(map);
  if (type === 'organisation') return organisationsApi.list(params).then(map);
  return usersApi.list(params).then(map);
}

/** Sélecteur de périmètre lisible (type + élément chargé depuis l'API). */
function AttachableField({ value, onChange }: { value: { type: string; id: string }; onChange: (v: { type: string; id: string }) => void }) {
  const type = (value.type || 'workplace') as AttachableAlias;
  const { data } = useQuery({
    queryKey: ['attachable-options', type],
    queryFn: () => loadAttachableOptions(type),
    enabled: !!type,
  });
  return (
    <div className="grid grid-cols-1 gap-3 md:col-span-2">
      <p className="text-xs font-medium text-muted-foreground">Périmètre de suivi</p>
      <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
        <label className="flex flex-col gap-1 text-sm">
          <span>Type de périmètre</span>
          <select value={type} onChange={(e) => onChange({ type: e.target.value, id: '' })} className="rounded border border-border bg-background px-2 py-1.5 text-sm">
            {Object.entries(ATTACHABLE_LABELS).map(([t, l]) => <option key={t} value={t}>{l}</option>)}
          </select>
        </label>
        <label className="flex flex-col gap-1 text-sm">
          <span>Élément rattaché</span>
          <select value={value.id} onChange={(e) => onChange({ type, id: e.target.value })} className="rounded border border-border bg-background px-2 py-1.5 text-sm">
            <option value="">— Choisir —</option>
            {(data ?? []).map((o) => <option key={o.id} value={o.id}>{o.name}</option>)}
          </select>
        </label>
      </div>
      <p className="text-xs text-muted-foreground">Laissez vide pour un suivi global ; renseignez un projet dans le champ « Projet » si cet OKR appartient à un projet.</p>
    </div>
  );
}

/** Sélecteur de projet lisible (OKR rattaché à un projet). */
function ProjectField({ value, onChange }: { value?: string; onChange: (v: string) => void }) {
  const { data } = useQuery({ queryKey: ['project-options'], queryFn: () => api.projectsApi.list({ 'page.size': 200 } as never) });
  const projects = (data?.data ?? []) as Entity[];
  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>Projet rattaché</span>
      <select value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm">
        <option value="">— Aucun (OKR global) —</option>
        {projects.map((p) => <option key={String(p.id)} value={String(p.id)}>{String(p.code ?? '')} — {String(p.name ?? '')}</option>)}
      </select>
    </label>
  );
}

// ---------------------------------------------------------------------------
// Primitives partagées (mêmes idiomes que les autres features — voir workflow/pages.tsx)
// ---------------------------------------------------------------------------

function makeList(r: ResourceApi, key: string, columns: TableColumn<Entity>[], o: { title: string; create?: string; del?: boolean }) {
  return function List() {
    const [page, setPage] = useState(1);
    const { data, isLoading, isError } = useResourceList(r, key, { page, 'page.size': 20 } as never);
    const destroy = useDestroy(r, key);
    const rows = (data?.data ?? []) as Entity[];
    const m = data?.meta;
    return (
      <div className="flex h-full flex-col gap-4">
        <PageHeader
          title={o.title}
          actions={o.create ? <Link href={o.create} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link> : undefined}
        />
        <DataTable
          columns={columns}
          rows={rows}
          loading={isLoading}
          error={isError}
          actions={o.del ? (row) => (
            <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">
              Supprimer
            </button>
          ) : undefined}
        />
        <Pagination page={page} totalPages={m?.last_page ?? 1} total={m?.total} onChange={setPage} />
      </div>
    );
  };
}

function Field({ label, value, onChange, required, type = 'text', options, placeholder }: {
  label: string; value?: string; onChange: (v: string) => void; required?: boolean; type?: string; options?: { value: string; label: string }[]; placeholder?: string;
}) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>{label} {required && <span className="text-danger">*</span>}</span>
      {options ? (
        <select value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm">
          <option value="">—</option>
          {options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
        </select>
      ) : (
        <input type={type} placeholder={placeholder} value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
      )}
    </label>
  );
}

function attachableLabel(row: Entity): string {
  const type = row.attachable_type as AttachableAlias | undefined;
  const attachable = row.attachable as { label?: string } | undefined;
  if (!type) return '—';
  return `${ATTACHABLE_LABELS[type] ?? type}${attachable?.label ? ` — ${attachable.label}` : ` #${row.attachable_id}`}`;
}

function statusBadge(value: unknown): React.ReactNode {
  const s = String(value ?? '');
  const styles: Record<string, string> = {
    on_track: 'bg-green-100 text-green-700',
    at_risk: 'bg-orange-100 text-orange-700',
    off_track: 'bg-red-100 text-red-700',
    done: 'bg-emerald-100 text-emerald-700',
    active: 'bg-blue-100 text-blue-700',
    on_hold: 'bg-yellow-100 text-yellow-700',
    completed: 'bg-emerald-100 text-emerald-700',
    archived: 'bg-muted text-muted-foreground',
    draft: 'bg-muted text-muted-foreground',
  };
  return <span className={`rounded px-1.5 py-0.5 text-xs ${styles[s] ?? 'bg-muted text-muted-foreground'}`}>{s}</span>;
}

function progressBar(progress: unknown): React.ReactNode {
  const pct = Math.round(Number(progress ?? 0) * 100);
  return (
    <div className="flex items-center gap-2">
      <div className="h-2 w-24 overflow-hidden rounded-full bg-muted">
        <div className="h-full bg-primary" style={{ width: `${Math.min(100, Math.max(0, pct))}%` }} />
      </div>
      <span className="text-xs text-muted-foreground">{pct}%</span>
    </div>
  );
}

// ---------------------------------------------------------------------------
// Projets
// ---------------------------------------------------------------------------

const PROJECT_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/projects/${r.id}`} className="hover:underline">{String(r.name ?? '')}</Link> },
  { key: 'status', label: 'Statut' },
  { key: 'attachable', label: 'Rattaché à', render: attachableLabel },
];

function ProjectForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const { data } = useResource(api.projectsApi, 'projects', id);
  const create = useCreate(api.projectsApi, 'projects');
  const update = useUpdate(api.projectsApi, 'projects');
  const [v, setV] = useState<Record<string, string>>({ attachable_type: 'workplace', attachable_id: '' });
  if (mode === 'edit' && data?.data && Object.keys(v).length <= 3) {
    const d = data.data;
    setV({
      code: String(d.code ?? ''), name: String(d.name ?? ''), description: String(d.description ?? ''),
      status: String(d.status ?? 'draft'),
      attachable_type: String(d.attachable_type ?? 'workplace'), attachable_id: String(d.attachable_id ?? ''),
    });
  }
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    const payload: Record<string, unknown> = {
      code: v.code, name: v.name, description: v.description, status: v.status || 'draft',
      attachable_type: v.attachable_id ? v.attachable_type : undefined,
      attachable_id: v.attachable_id || undefined,
    };
    if (mode === 'edit' && id) await update.mutateAsync({ id, payload });
    else await create.mutateAsync(payload);
    router.push('/projects');
  }
  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <PageHeader
        title={mode === 'edit' ? 'Modifier — projet' : 'Nouveau projet'}
        description="Un projet : un chantier suivi avec objectifs (OKR), KPI, jalons, livrables, ressources et rapports d'étape."
        actions={<button type="button" onClick={() => router.push('/projects')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>}
      />
      <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
        <Field label="Code" value={v.code} onChange={(x) => setV((p) => ({ ...p, code: x }))} required={mode === 'create'} />
        <Field label="Nom *" value={v.name} onChange={(x) => setV((p) => ({ ...p, name: x }))} required />
        <Field label="Description" value={v.description} onChange={(x) => setV((p) => ({ ...p, description: x }))} />
        <Field label="Statut" value={v.status} onChange={(x) => setV((p) => ({ ...p, status: x }))}
          options={[{ value: 'draft', label: 'Brouillon' }, { value: 'active', label: 'En cours' }, { value: 'on_hold', label: 'En pause' }, { value: 'completed', label: 'Terminé' }, { value: 'archived', label: 'Archivé' }]} />
        <AttachableField value={{ type: v.attachable_type ?? 'workplace', id: v.attachable_id ?? '' }} onChange={(a) => setV((p) => ({ ...p, attachable_type: a.type, attachable_id: a.id }))} />
      </div>
      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
    </form>
  );
}

// ---------------------------------------------------------------------------
// Extension MS-Project-parity : jalons, livrables, ressources, alertes,
// rapports d'étape — panneaux de la fiche projet (voir ProjectDetail).
// ---------------------------------------------------------------------------

const MILESTONE_STATUS_LABELS: Record<string, string> = { pending: 'En attente', reached: 'Atteint', missed: 'Manqué' };
const DELIVERABLE_STATUS_LABELS: Record<string, string> = { draft: 'Brouillon', submitted: 'Soumis', approved: 'Approuvé', rejected: 'Rejeté' };
const ALERT_SEVERITY_STYLES: Record<string, string> = { high: 'border-danger/40 bg-danger/10 text-danger', medium: 'border-warning/40 bg-warning/10 text-warning', low: 'border-border bg-muted text-muted-foreground' };

function AlertsPanel({ projectId }: { projectId: string }) {
  const { data } = useQuery({ queryKey: ['project-alerts', projectId], queryFn: () => api.getProjectAlerts(projectId) });
  const alerts = (data?.data ?? []) as Entity[];
  return (
    <div className="rounded border border-border bg-surface p-4">
      <h2 className="mb-2 text-sm font-semibold">Alertes</h2>
      {alerts.length === 0 ? (
        <p className="text-sm text-muted-foreground">Aucune alerte — tout est sous contrôle.</p>
      ) : (
        <ul className="flex flex-col gap-2 text-sm">
          {alerts.map((a, i) => (
            <li key={i} className={`rounded border px-3 py-2 ${ALERT_SEVERITY_STYLES[String(a.severity)] ?? ALERT_SEVERITY_STYLES.low}`}>
              {String(a.message)}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

function MilestonesPanel({ projectId }: { projectId: string }) {
  const queryClient = useQueryClient();
  const { data } = useQuery({ queryKey: ['project-milestones', projectId], queryFn: () => api.getProjectMilestones(projectId) });
  const milestones = (data?.data ?? []) as Entity[];
  const [v, setV] = useState({ name: '', due_date: '' });

  function invalidate() {
    queryClient.invalidateQueries({ queryKey: ['project-milestones', projectId] });
    queryClient.invalidateQueries({ queryKey: ['project-alerts', projectId] });
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    await api.createProjectMilestone(projectId, { name: v.name, due_date: v.due_date || undefined });
    setV({ name: '', due_date: '' });
    invalidate();
  }

  async function reach(milestoneId: string) {
    await api.reachProjectMilestone(milestoneId);
    invalidate();
  }

  return (
    <div className="rounded border border-border bg-surface p-4">
      <h2 className="mb-2 text-sm font-semibold">Jalons</h2>
      {milestones.length === 0 ? (
        <p className="text-sm text-muted-foreground">Aucun jalon.</p>
      ) : (
        <ul className="divide-y divide-border text-sm">
          {milestones.map((m) => (
            <li key={String(m.id)} className="flex items-center justify-between py-2">
              <span className={m.is_overdue ? 'text-danger' : ''}>{String(m.name)}</span>
              <span className="flex items-center gap-2">
                <span className="text-xs text-muted-foreground">
                  {m.due_date ? new Date(String(m.due_date)).toLocaleDateString('fr-FR') : '—'} · {MILESTONE_STATUS_LABELS[String(m.status)] ?? String(m.status)}
                </span>
                {m.status === 'pending' && (
                  <button type="button" onClick={() => reach(String(m.id))} className="rounded border border-border px-2 py-0.5 text-xs hover:bg-muted">Atteint</button>
                )}
              </span>
            </li>
          ))}
        </ul>
      )}
      <form onSubmit={submit} className="mt-3 flex flex-wrap items-end gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-col gap-1"><span>Nom</span><input value={v.name} onChange={(e) => setV((p) => ({ ...p, name: e.target.value }))} required className="rounded border border-border bg-background px-2 py-1.5" /></label>
        <label className="flex flex-col gap-1"><span>Échéance</span><input type="date" value={v.due_date} onChange={(e) => setV((p) => ({ ...p, due_date: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5" /></label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground">Ajouter un jalon</button>
      </form>
    </div>
  );
}

function DeliverablesPanel({ projectId }: { projectId: string }) {
  const queryClient = useQueryClient();
  const { data } = useQuery({ queryKey: ['project-deliverables', projectId], queryFn: () => api.getProjectDeliverables(projectId) });
  const deliverables = (data?.data ?? []) as Entity[];
  const [v, setV] = useState({ name: '', due_date: '' });

  function invalidate() {
    queryClient.invalidateQueries({ queryKey: ['project-deliverables', projectId] });
    queryClient.invalidateQueries({ queryKey: ['project-alerts', projectId] });
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    await api.createProjectDeliverable(projectId, { name: v.name, due_date: v.due_date || undefined });
    setV({ name: '', due_date: '' });
    invalidate();
  }

  async function act(fn: (id: string) => Promise<unknown>, deliverableId: string) {
    await fn(deliverableId);
    invalidate();
  }

  return (
    <div className="rounded border border-border bg-surface p-4">
      <h2 className="mb-2 text-sm font-semibold">Livrables</h2>
      {deliverables.length === 0 ? (
        <p className="text-sm text-muted-foreground">Aucun livrable.</p>
      ) : (
        <ul className="divide-y divide-border text-sm">
          {deliverables.map((d) => (
            <li key={String(d.id)} className="flex items-center justify-between py-2">
              <span className={d.is_overdue ? 'text-danger' : ''}>{String(d.name)}</span>
              <span className="flex items-center gap-2">
                <span className="text-xs text-muted-foreground">{DELIVERABLE_STATUS_LABELS[String(d.status)] ?? String(d.status)}</span>
                {d.status === 'draft' && (
                  <button type="button" onClick={() => act(api.submitProjectDeliverable, String(d.id))} className="rounded border border-border px-2 py-0.5 text-xs hover:bg-muted">Soumettre</button>
                )}
                {d.status === 'submitted' && (
                  <>
                    <button type="button" onClick={() => act(api.approveProjectDeliverable, String(d.id))} className="rounded border border-border px-2 py-0.5 text-xs hover:bg-muted">Approuver</button>
                    <button type="button" onClick={() => act(api.rejectProjectDeliverable, String(d.id))} className="rounded border border-border px-2 py-0.5 text-xs text-danger hover:bg-muted">Rejeter</button>
                  </>
                )}
              </span>
            </li>
          ))}
        </ul>
      )}
      <form onSubmit={submit} className="mt-3 flex flex-wrap items-end gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-col gap-1"><span>Nom</span><input value={v.name} onChange={(e) => setV((p) => ({ ...p, name: e.target.value }))} required className="rounded border border-border bg-background px-2 py-1.5" /></label>
        <label className="flex flex-col gap-1"><span>Échéance</span><input type="date" value={v.due_date} onChange={(e) => setV((p) => ({ ...p, due_date: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5" /></label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground">Ajouter un livrable</button>
      </form>
    </div>
  );
}

function ResourcesPanel({ projectId }: { projectId: string }) {
  const queryClient = useQueryClient();
  const { data } = useQuery({ queryKey: ['project-resources', projectId], queryFn: () => api.getProjectResources(projectId) });
  const resources = (data?.data ?? []) as Entity[];
  const [v, setV] = useState({ type: 'human', name: '', planned_amount: '', actual_amount: '' });

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    await api.createProjectResource(projectId, {
      type: v.type,
      name: v.name,
      planned_amount: v.planned_amount || undefined,
      actual_amount: v.actual_amount || undefined,
    });
    setV({ type: 'human', name: '', planned_amount: '', actual_amount: '' });
    queryClient.invalidateQueries({ queryKey: ['project-resources', projectId] });
    queryClient.invalidateQueries({ queryKey: ['projects', projectId] });
    queryClient.invalidateQueries({ queryKey: ['project-alerts', projectId] });
  }

  return (
    <div className="rounded border border-border bg-surface p-4">
      <h2 className="mb-2 text-sm font-semibold">Ressources</h2>
      {resources.length === 0 ? (
        <p className="text-sm text-muted-foreground">Aucune ressource.</p>
      ) : (
        <ul className="divide-y divide-border text-sm">
          {resources.map((r) => (
            <li key={String(r.id)} className="flex items-center justify-between py-2">
              <span>{String(r.name)} <span className="text-xs text-muted-foreground">({RESOURCE_TYPE_LABELS[String(r.type)] ?? String(r.type)})</span></span>
              <span className={`text-xs ${r.is_over_budget ? 'text-danger' : 'text-muted-foreground'}`}>
                {r.actual_amount !== null && r.actual_amount !== undefined ? String(r.actual_amount) : '—'} / {r.planned_amount !== null && r.planned_amount !== undefined ? String(r.planned_amount) : '—'}
              </span>
            </li>
          ))}
        </ul>
      )}
      <form onSubmit={submit} className="mt-3 flex flex-wrap items-end gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-col gap-1">
          <span>Type</span>
          <select value={v.type} onChange={(e) => setV((p) => ({ ...p, type: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5">
            {Object.entries(RESOURCE_TYPE_LABELS).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
          </select>
        </label>
        <label className="flex flex-col gap-1"><span>Nom</span><input value={v.name} onChange={(e) => setV((p) => ({ ...p, name: e.target.value }))} required className="rounded border border-border bg-background px-2 py-1.5" /></label>
        <label className="flex flex-col gap-1"><span>Planifié</span><input type="number" value={v.planned_amount} onChange={(e) => setV((p) => ({ ...p, planned_amount: e.target.value }))} className="w-24 rounded border border-border bg-background px-2 py-1.5" /></label>
        <label className="flex flex-col gap-1"><span>Réel</span><input type="number" value={v.actual_amount} onChange={(e) => setV((p) => ({ ...p, actual_amount: e.target.value }))} className="w-24 rounded border border-border bg-background px-2 py-1.5" /></label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground">Ajouter une ressource</button>
      </form>
    </div>
  );
}

function StatusReportsPanel({ projectId }: { projectId: string }) {
  const queryClient = useQueryClient();
  const { data } = useQuery({ queryKey: ['project-status-reports', projectId], queryFn: () => api.getProjectStatusReports(projectId) });
  const reports = (data?.data ?? []) as Entity[];
  const [v, setV] = useState({ percent_complete: '', summary: '' });

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    await api.createProjectStatusReport(projectId, { percent_complete: v.percent_complete, summary: v.summary });
    setV({ percent_complete: '', summary: '' });
    queryClient.invalidateQueries({ queryKey: ['project-status-reports', projectId] });
  }

  return (
    <div className="rounded border border-border bg-surface p-4">
      <h2 className="mb-2 text-sm font-semibold">Rapports d&apos;étape</h2>
      {reports.length === 0 ? (
        <p className="text-sm text-muted-foreground">Aucun rapport.</p>
      ) : (
        <ul className="divide-y divide-border text-sm">
          {reports.map((r) => (
            <li key={String(r.id)} className="flex flex-col gap-1 py-2">
              <div className="flex items-center justify-between">
                <span className="text-xs text-muted-foreground">{r.reported_at ? new Date(String(r.reported_at)).toLocaleDateString('fr-FR') : '—'}</span>
                <span className="font-medium">{String(r.percent_complete)}%</span>
              </div>
              <p>{String(r.summary)}</p>
            </li>
          ))}
        </ul>
      )}
      <form onSubmit={submit} className="mt-3 flex flex-wrap items-end gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-col gap-1"><span>Avancement (%)</span><input type="number" min={0} max={100} value={v.percent_complete} onChange={(e) => setV((p) => ({ ...p, percent_complete: e.target.value }))} required className="w-24 rounded border border-border bg-background px-2 py-1.5" /></label>
        <label className="flex flex-1 flex-col gap-1"><span>Résumé</span><input value={v.summary} onChange={(e) => setV((p) => ({ ...p, summary: e.target.value }))} required className="rounded border border-border bg-background px-2 py-1.5" /></label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground">Ajouter un rapport</button>
      </form>
    </div>
  );
}

function ProjectDetail({ id }: { id: string }) {
  const { data } = useResource(api.projectsApi, 'projects', id);
  const { data: tasksData } = useQuery({ queryKey: ['project-tasks', id], queryFn: () => api.getProjectTasks(id) });
  const { data: objectivesData } = useResourceList(api.objectivesApi, 'objectives', { 'filter[project_id]': id } as never);
  const project = data?.data;
  const tasks = tasksData?.data ?? [];
  const objectives = (objectivesData?.data ?? []) as Entity[];

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader
        title={project ? String(project.name) : 'Projet'}
        description={project ? String(project.description ?? '') : undefined}
        actions={<Link href={`/projects/${id}/edit`} className="rounded border border-border px-3 py-1.5 text-sm">Modifier</Link>}
      />
      {project && (
        <InfoPanel title="Informations" items={[
          ['Code', String(project.code)],
          ['Statut', String(project.status)],
          ['Rattaché à', attachableLabel(project)],
          ['Objectifs', String(objectives.length)],
          ['Tâches', String(tasks.length)],
          ['Budget planifié', project.budget_planned !== null && project.budget_planned !== undefined ? String(project.budget_planned) : '—'],
          ['Budget consommé', project.budget_actual !== undefined ? String(project.budget_actual) : '—'],
        ]} />
      )}
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div className="rounded border border-border bg-surface p-4">
          <h2 className="mb-2 text-sm font-semibold">Objectifs (OKR)</h2>
          {objectives.length === 0 ? <p className="text-sm text-muted-foreground">Aucun objectif.</p> : (
            <ul className="divide-y divide-border text-sm">
              {objectives.map((o) => (
                <li key={String(o.id)} className="py-2">
                  <Link href={`/objectives/${o.id}`} className="hover:underline">{String(o.title)}</Link>
                  <span className="ml-2 text-xs text-muted-foreground">{String(o.status)}</span>
                </li>
              ))}
            </ul>
          )}
        </div>
        <div className="rounded border border-border bg-surface p-4">
          <h2 className="mb-2 text-sm font-semibold">Tâches</h2>
          {tasks.length === 0 ? <p className="text-sm text-muted-foreground">Aucune tâche.</p> : (
            <ul className="divide-y divide-border text-sm">
              {tasks.map((t) => (
                <li key={String(t.id)} className="flex items-center justify-between py-2">
                  <span>{String(t.title)}</span>
                  <span className="text-xs text-muted-foreground">{String(t.status)}</span>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
      <AlertsPanel projectId={id} />
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        <MilestonesPanel projectId={id} />
        <DeliverablesPanel projectId={id} />
        <ResourcesPanel projectId={id} />
        <StatusReportsPanel projectId={id} />
      </div>
    </div>
  );
}

// ---------------------------------------------------------------------------
// Objectifs (OKR) + Résultats clés
// ---------------------------------------------------------------------------

const OBJECTIVE_COLS: TableColumn<Entity>[] = [
  { key: 'title', label: 'Titre', render: (r) => <Link href={`/objectives/${r.id}`} className="hover:underline">{String(r.title ?? '')}</Link> },
  { key: 'status', label: 'Statut', render: (r) => statusBadge(r.status) },
  { key: 'progress', label: 'Progression', render: (r) => progressBar(r.progress) },
  { key: 'project', label: 'Projet', render: (r) => (r.project_id ? <Link href={`/projects/${r.project_id}`} className="hover:underline text-primary">{String((r.project as { name?: string })?.name ?? `Projet #${r.project_id}`)}</Link> : <span className="text-muted-foreground">OKR global</span>) },
  { key: 'period', label: 'Période', render: (r) => {
    const s = r.period_start ? new Date(String(r.period_start)).toLocaleDateString('fr-FR') : '—';
    const e = r.period_end ? new Date(String(r.period_end)).toLocaleDateString('fr-FR') : '—';
    return <span className="text-xs text-muted-foreground">{s} → {e}</span>;
  } },
];

function ObjectiveForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const { data } = useResource(api.objectivesApi, 'objectives', id);
  const create = useCreate(api.objectivesApi, 'objectives');
  const update = useUpdate(api.objectivesApi, 'objectives');
  const [v, setV] = useState<Record<string, string>>({ project_id: '', attachable_type: 'workplace', attachable_id: '' });
  if (mode === 'edit' && data?.data && Object.keys(v).length <= 3) {
    const d = data.data;
    setV({
      title: String(d.title ?? ''), description: String(d.description ?? ''),
      project_id: String(d.project_id ?? ''), status: String(d.status ?? 'on_track'),
      period_start: String(d.period_start ?? ''), period_end: String(d.period_end ?? ''),
      attachable_type: String(d.attachable_type ?? 'workplace'), attachable_id: String(d.attachable_id ?? ''),
    });
  }
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    const payload: Record<string, unknown> = {
      title: v.title, description: v.description, status: v.status || 'on_track',
      period_start: v.period_start || undefined, period_end: v.period_end || undefined,
      project_id: v.project_id || undefined,
      attachable_type: v.attachable_id ? v.attachable_type : undefined,
      attachable_id: v.attachable_id || undefined,
    };
    if (mode === 'edit' && id) await update.mutateAsync({ id, payload });
    else await create.mutateAsync(payload);
    router.push('/objectives');
  }
  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <PageHeader
        title={mode === 'edit' ? 'Modifier — objectif (OKR)' : 'Nouvel objectif (OKR)'}
        description="Un objectif (OKR) : un résultat visé, suivi par des résultats clés. Rattachez-le à un projet pour un OKR de projet, ou laissez-le global."
        actions={<button type="button" onClick={() => router.push('/objectives')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>}
      />
      <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
        <Field label="Titre de l'objectif *" value={v.title} onChange={(x) => setV((p) => ({ ...p, title: x }))} required />
        <Field label="Description" value={v.description} onChange={(x) => setV((p) => ({ ...p, description: x }))} />
        <ProjectField value={v.project_id} onChange={(x) => setV((p) => ({ ...p, project_id: x }))} />
        <Field label="Début de période" type="date" value={v.period_start} onChange={(x) => setV((p) => ({ ...p, period_start: x }))} />
        <Field label="Fin de période" type="date" value={v.period_end} onChange={(x) => setV((p) => ({ ...p, period_end: x }))} />
        <Field label="Statut" value={v.status} onChange={(x) => setV((p) => ({ ...p, status: x }))}
          options={[{ value: 'on_track', label: 'Sur la bonne voie' }, { value: 'at_risk', label: 'À risque' }, { value: 'off_track', label: 'En dérive' }, { value: 'done', label: 'Terminé' }]} />
        <AttachableField value={{ type: v.attachable_type ?? 'workplace', id: v.attachable_id ?? '' }} onChange={(a) => setV((p) => ({ ...p, attachable_type: a.type, attachable_id: a.id }))} />
      </div>
      <p className="text-xs text-muted-foreground">Les résultats clés (avec cibles et progression) s'ajoutent depuis la fiche de l'objectif, une fois créé.</p>
      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
    </form>
  );
}

function KeyResultRow({ objectiveId, kr }: { objectiveId: string; kr: Entity }) {
  const queryClient = useQueryClient();
  const update = useUpdate(api.keyResultsApi, 'key-results');
  const [value, setValue] = useState(String(kr.current_value ?? ''));
  const progress = Math.round(Number(kr.progress ?? 0) * 100);

  async function save() {
    await update.mutateAsync({ id: String(kr.id), payload: { current_value: value } });
    queryClient.invalidateQueries({ queryKey: ['objectives', objectiveId] });
  }

  return (
    <li className="flex flex-col gap-2 py-3">
      <div className="flex items-center justify-between text-sm">
        <span>{String(kr.title)}</span>
        <span className="text-xs text-muted-foreground">{progress}%</span>
      </div>
      <div className="h-2 w-full overflow-hidden rounded-full bg-muted">
        <div className="h-full bg-primary" style={{ width: `${Math.min(100, Math.max(0, progress))}%` }} />
      </div>
      <div className="flex items-center gap-2 text-xs">
        <span className="text-muted-foreground">
          {String(kr.start_value)} → {String(kr.target_value)} {String(kr.unit ?? '')}
        </span>
        <input value={value} onChange={(e) => setValue(e.target.value)} className="w-24 rounded border border-border bg-background px-2 py-1" />
        <button type="button" onClick={save} className="rounded border border-border px-2 py-1 hover:bg-muted">Mettre à jour</button>
      </div>
    </li>
  );
}

function AddKeyResultForm({ objectiveId }: { objectiveId: string }) {
  const queryClient = useQueryClient();
  const [v, setV] = useState({ title: '', target_value: '', unit: '' });
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    await api.createKeyResult(objectiveId, { title: v.title, target_value: v.target_value, unit: v.unit || undefined });
    setV({ title: '', target_value: '', unit: '' });
    queryClient.invalidateQueries({ queryKey: ['objectives', objectiveId] });
  }
  return (
    <form onSubmit={submit} className="mt-3 flex flex-wrap items-end gap-2 border-t border-border pt-3 text-sm">
      <label className="flex flex-col gap-1"><span>Titre</span><input value={v.title} onChange={(e) => setV((p) => ({ ...p, title: e.target.value }))} required className="rounded border border-border bg-background px-2 py-1.5" /></label>
      <label className="flex flex-col gap-1"><span>Cible</span><input type="number" value={v.target_value} onChange={(e) => setV((p) => ({ ...p, target_value: e.target.value }))} required className="w-28 rounded border border-border bg-background px-2 py-1.5" /></label>
      <label className="flex flex-col gap-1"><span>Unité</span><input value={v.unit} onChange={(e) => setV((p) => ({ ...p, unit: e.target.value }))} className="w-24 rounded border border-border bg-background px-2 py-1.5" /></label>
      <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground">Ajouter un résultat clé</button>
    </form>
  );
}

function ObjectiveDetail({ id }: { id: string }) {
  // `show()` du client générique ne prend pas de query params ; requête directe
  // pour inclure `key_results` (non chargés par défaut, voir ObjectiveResource).
  const { data: single } = useQuery({
    queryKey: ['objectives', id],
    queryFn: () => apiFetch<{ data: Entity }>(`/api/v1/objectives/${id}?include=keyResults`),
  });
  const objective = single?.data;
  const keyResults = (objective?.key_results as Entity[] | undefined) ?? [];

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader
        title={objective ? String(objective.title) : 'Objectif'}
        description={objective ? String(objective.description ?? '') : undefined}
        actions={<Link href={`/objectives/${id}/edit`} className="rounded border border-border px-3 py-1.5 text-sm">Modifier</Link>}
      />
      {objective && (
        <>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div className="rounded border border-border bg-surface p-4 text-center">
              <div className="text-2xl font-semibold">{Math.round(Number(objective.progress ?? 0) * 100)}%</div>
              <div className="mt-1 text-xs text-muted-foreground">Progression</div>
            </div>
            <div className="rounded border border-border bg-surface p-4 text-center">
              <div className="flex justify-center">{statusBadge(objective.status)}</div>
              <div className="mt-1 text-xs text-muted-foreground">Statut</div>
            </div>
            <div className="rounded border border-border bg-surface p-4 text-center">
              <div className="text-sm">{objective.project_id ? <Link href={`/projects/${objective.project_id}`} className="text-primary hover:underline">{String((objective.project as { name?: string })?.name ?? `Projet #${objective.project_id}`)}</Link> : 'OKR global'}</div>
              <div className="mt-1 text-xs text-muted-foreground">Projet rattaché</div>
            </div>
            <div className="rounded border border-border bg-surface p-4 text-center">
              <div className="text-sm">{objective.period_start && objective.period_end ? `${new Date(String(objective.period_start)).toLocaleDateString('fr-FR')} → ${new Date(String(objective.period_end)).toLocaleDateString('fr-FR')}` : '—'}</div>
              <div className="mt-1 text-xs text-muted-foreground">Période</div>
            </div>
          </div>
          <InfoPanel title="Informations" items={[
            ['Description', String(objective.description ?? '—')],
            ['Périmètre', attachableLabel(objective)],
          ]} />
        </>
      )}
      <div className="rounded border border-border bg-surface p-4">
        <h2 className="mb-2 text-sm font-semibold">Résultats clés</h2>
        {keyResults.length === 0 ? (
          <p className="text-sm text-muted-foreground">Aucun résultat clé pour le moment.</p>
        ) : (
          <ul className="divide-y divide-border">
            {keyResults.map((kr) => <KeyResultRow key={String(kr.id)} objectiveId={id} kr={kr} />)}
          </ul>
        )}
        <AddKeyResultForm objectiveId={id} />
      </div>
    </div>
  );
}

// ---------------------------------------------------------------------------
// KPI
// ---------------------------------------------------------------------------

const KPI_COLS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/kpis/${r.id}`} className="hover:underline">{String(r.name ?? '')}</Link> },
  { key: 'target_value', label: 'Cible', render: (r) => (r.target_value !== null && r.target_value !== undefined ? `${r.target_value} ${r.unit ?? ''}` : '—') },
  { key: 'direction', label: 'Objectif', render: (r) => <span className="rounded bg-muted px-1.5 py-0.5 text-xs">{r.direction === 'lower_is_better' ? 'Plus bas = mieux' : 'Plus haut = mieux'}</span> },
  { key: 'frequency', label: 'Fréquence' },
  { key: 'attachable', label: 'Périmètre', render: attachableLabel },
];

function KpiForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const { data } = useResource(api.kpisApi, 'kpis', id);
  const create = useCreate(api.kpisApi, 'kpis');
  const update = useUpdate(api.kpisApi, 'kpis');
  const [v, setV] = useState<Record<string, string>>({ attachable_type: 'workplace', attachable_id: '' });
  if (mode === 'edit' && data?.data && Object.keys(v).length <= 3) {
    const d = data.data;
    setV({
      code: String(d.code ?? ''), name: String(d.name ?? ''), description: String(d.description ?? ''),
      unit: String(d.unit ?? ''), target_value: String(d.target_value ?? ''), direction: String(d.direction ?? 'higher_is_better'),
      frequency: String(d.frequency ?? 'monthly'),
      attachable_type: String(d.attachable_type ?? 'workplace'), attachable_id: String(d.attachable_id ?? ''),
    });
  }
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    const payload: Record<string, unknown> = {
      code: v.code, name: v.name, description: v.description, unit: v.unit,
      target_value: v.target_value !== '' ? v.target_value : undefined,
      direction: v.direction || 'higher_is_better', frequency: v.frequency || 'monthly',
      attachable_type: v.attachable_id ? v.attachable_type : undefined,
      attachable_id: v.attachable_id || undefined,
    };
    if (mode === 'edit' && id) await update.mutateAsync({ id, payload });
    else await create.mutateAsync(payload);
    router.push('/kpis');
  }
  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <PageHeader
        title={mode === 'edit' ? 'Modifier — KPI' : 'Nouveau KPI'}
        description="Un indicateur clé de performance : une mesure chiffrée suivie régulièrement (valeur cible, fréquence, sens du « mieux »)."
        actions={<button type="button" onClick={() => router.push('/kpis')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>}
      />
      <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
        <Field label="Code *" value={v.code} onChange={(x) => setV((p) => ({ ...p, code: x }))} required={mode === 'create'} />
        <Field label="Nom de l'indicateur *" value={v.name} onChange={(x) => setV((p) => ({ ...p, name: x }))} required />
        <Field label="Description" value={v.description} onChange={(x) => setV((p) => ({ ...p, description: x }))} />
        <Field label="Unité" value={v.unit} onChange={(x) => setV((p) => ({ ...p, unit: x }))} placeholder="ex. %, jours, €" />
        <Field label="Valeur cible" type="number" value={v.target_value} onChange={(x) => setV((p) => ({ ...p, target_value: x }))} />
        <Field label="Le « mieux » est…" value={v.direction} onChange={(x) => setV((p) => ({ ...p, direction: x }))}
          options={[{ value: 'higher_is_better', label: 'Une valeur plus haute' }, { value: 'lower_is_better', label: 'Une valeur plus basse' }]} />
        <Field label="Fréquence de mesure" value={v.frequency} onChange={(x) => setV((p) => ({ ...p, frequency: x }))}
          options={['daily', 'weekly', 'monthly', 'quarterly', 'yearly'].map((f) => ({ value: f, label: f }))} />
        <AttachableField value={{ type: v.attachable_type ?? 'workplace', id: v.attachable_id ?? '' }} onChange={(a) => setV((p) => ({ ...p, attachable_type: a.type, attachable_id: a.id }))} />
      </div>
      <p className="text-xs text-muted-foreground">Les mesures (valeurs saisies périodiquement) se saisissent depuis la fiche du KPI.</p>
      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
    </form>
  );
}

function AddMeasurementForm({ kpiId, onAdded }: { kpiId: string; onAdded: () => void }) {
  const [value, setValue] = useState('');
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    await api.recordKpiMeasurement(kpiId, { value: Number(value) });
    setValue('');
    onAdded();
  }
  return (
    <form onSubmit={submit} className="flex items-end gap-2 border-t border-border pt-3 text-sm">
      <label className="flex flex-col gap-1"><span>Nouvelle mesure</span><input type="number" step="any" value={value} onChange={(e) => setValue(e.target.value)} required className="w-32 rounded border border-border bg-background px-2 py-1.5" /></label>
      <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground">Enregistrer</button>
    </form>
  );
}

function KpiDetail({ id }: { id: string }) {
  const { data } = useResource(api.kpisApi, 'kpis', id);
  const queryClient = useQueryClient();
  const { data: measurements } = useQuery({
    queryKey: ['kpi-measurements', id],
    queryFn: () => api.getKpiMeasurements(id),
  });
  const kpi = data?.data;
  const rows = (measurements?.data ?? []) as Entity[];
  const latest = rows[0];
  const previous = rows[1];
  const trend = latest && previous ? Number(latest.value) - Number(previous.value) : null;

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader
        title={kpi ? String(kpi.name) : 'KPI'}
        description={kpi ? String(kpi.description ?? '') : undefined}
        actions={<Link href={`/kpis/${id}/edit`} className="rounded border border-border px-3 py-1.5 text-sm">Modifier</Link>}
      />
      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        <StatCard label="Dernière mesure" value={latest ? `${latest.value} ${kpi?.unit ?? ''}` : '—'} />
        <StatCard label="Tendance" value={trend === null ? '—' : trend > 0 ? '↑' : trend < 0 ? '↓' : '→'} accent={trend !== null && trend !== 0 ? (kpi?.direction === 'lower_is_better' ? (trend < 0 ? 'ok' : 'danger') : (trend > 0 ? 'ok' : 'danger')) : undefined} />
        <StatCard label="Cible" value={kpi?.target_value !== undefined && kpi?.target_value !== null ? `${kpi.target_value} ${kpi.unit ?? ''}` : '—'} />
        <StatCard label="Rattaché à" value={kpi ? attachableLabel(kpi) : '—'} />
      </div>
      <div className="rounded border border-border bg-surface p-4">
        <h2 className="mb-2 text-sm font-semibold">Historique des mesures</h2>
        {rows.length === 0 ? (
          <p className="text-sm text-muted-foreground">Aucune mesure enregistrée.</p>
        ) : (
          <ul className="divide-y divide-border text-sm">
            {rows.map((m) => (
              <li key={String(m.id)} className="flex items-center justify-between py-2">
                <span>{m.measured_at ? new Date(String(m.measured_at)).toLocaleDateString('fr-FR') : '—'}</span>
                <span className="font-medium">{String(m.value)} {String(kpi?.unit ?? '')}</span>
              </li>
            ))}
          </ul>
        )}
        <AddMeasurementForm kpiId={id} onAdded={() => queryClient.invalidateQueries({ queryKey: ['kpi-measurements', id] })} />
      </div>
    </div>
  );
}

// ---------------------------------------------------------------------------

export const routes: FeatureRoute[] = [
  { path: '/projects', List: makeList(api.projectsApi, 'projects', PROJECT_COLS, { title: 'Projets', create: '/projects/create', del: true }), Detail: ProjectDetail, Form: ProjectForm },
  { path: '/objectives', List: makeList(api.objectivesApi, 'objectives', OBJECTIVE_COLS, { title: 'Objectifs (OKR)', create: '/objectives/create', del: true }), Detail: ObjectiveDetail, Form: ObjectiveForm },
  { path: '/kpis', List: makeList(api.kpisApi, 'kpis', KPI_COLS, { title: 'KPI', create: '/kpis/create', del: true }), Detail: KpiDetail, Form: KpiForm },

  // Suivi global (agrégation de tous les projets)
  { path: '/projects/milestones', List: GlobalMilestonesList },
  { path: '/projects/deliverables', List: GlobalDeliverablesList },
  { path: '/projects/alerts', List: GlobalAlertsList },
  { path: '/projects/resources', List: GlobalResourcesList },

  // Visualisations
  { path: '/projects/board', List: KanbanBoard },
  { path: '/projects/gantt', List: GanttChart },
];
