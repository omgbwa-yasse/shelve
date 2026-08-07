'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useQuery } from '@tanstack/react-query';
import { PageHeader } from '@/components/ui/page';
import { DataTable, type TableColumn } from '@/components/ui/table';
import { apiFetch } from '@/lib/api/client';
import type { Entity, ListEnvelope } from '@/lib/api/types';
import * as api from '../services/project.service';

/* ------------------------------------------------------------------ */
/* Agrégation : toutes les sous-ressources de tous les projets         */
/* ------------------------------------------------------------------ */
type Aggregated<T> = { project: Entity; items: T[] }[];

async function aggregate<T extends Entity>(fetchSub: (id: string) => Promise<ListEnvelope<T>>): Promise<Aggregated<T>> {
  const projects = (await api.projectsApi.list({ 'page.size': 200 } as never)).data ?? [];
  const rows: Aggregated<T> = [];
  for (const p of projects) {
    const res = await fetchSub(String(p.id));
    const items = (res.data ?? []) as T[];
    if (items.length) rows.push({ project: p, items });
  }
  return rows;
}

function GlobalList<T extends Entity>({
  title,
  description,
  queryKey,
  fetcher,
  columns,
  projectLabel = (p) => String(p.name ?? p.code ?? p.id),
  empty = 'Aucun élément.',
}: {
  title: string;
  description: string;
  queryKey: string;
  fetcher: (id: string) => Promise<ListEnvelope<T>>;
  columns: TableColumn<T>[];
  projectLabel?: (p: Entity) => string;
  empty?: string;
}) {
  const { data, isLoading } = useQuery({ queryKey: [queryKey], queryFn: () => aggregate<T>(fetcher) });
  const rows = data ?? [];

  const flat: (T & { __project: Entity })[] = rows.flatMap((r) => r.items.map((item) => ({ ...item, __project: r.project })));

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title={title} description={description} />
      <DataTable
        columns={[
          { key: '__project', label: 'Projet', render: (r) => <Link href={`/projects/${(r as { __project: Entity }).__project.id}`} className="text-primary hover:underline">{projectLabel((r as { __project: Entity }).__project)}</Link> },
          ...columns,
        ]}
        rows={flat}
        loading={isLoading}
        emptyLabel={empty}
      />
    </div>
  );
}

/* ------------------------------------------------------------------ */
/* Suivi global : livrables, jalons, alertes, ressources               */
/* ------------------------------------------------------------------ */

const STATUS_BADGE: Record<string, string> = {
  draft: 'bg-muted text-muted-foreground', submitted: 'bg-blue-100 text-blue-700',
  approved: 'bg-green-100 text-green-700', rejected: 'bg-red-100 text-red-700',
  pending: 'bg-yellow-100 text-yellow-700', reached: 'bg-green-100 text-green-700', missed: 'bg-red-100 text-red-700',
  high: 'bg-red-100 text-red-700', medium: 'bg-orange-100 text-orange-700', low: 'bg-muted text-muted-foreground',
  open: 'bg-yellow-100 text-yellow-700', mitigated: 'bg-blue-100 text-blue-700',
  closed: 'bg-green-100 text-green-700', occurred: 'bg-red-100 text-red-700',
};

function status(value: unknown) {
  const s = String(value ?? '');
  return <span className={`rounded px-1.5 py-0.5 text-xs ${STATUS_BADGE[s] ?? 'bg-muted text-muted-foreground'}`}>{s}</span>;
}

export function GlobalMilestonesList() {
  return (
    <GlobalList
      title="Tous les jalons"
      description="Jalons de tous les projets — état d'avancement global."
      queryKey="all-milestones"
      fetcher={(id) => api.getProjectMilestones(id)}
      empty="Aucun jalon."
      columns={[
        { key: 'name', label: 'Jalon', render: (r) => String(r.name ?? '') },
        { key: 'due_date', label: 'Échéance', render: (r) => (r.due_date ? new Date(String(r.due_date)).toLocaleDateString('fr-FR') : '—') },
        { key: 'status', label: 'Statut', render: (r) => status(r.status) },
        { key: 'is_overdue', label: '', render: (r) => (r.is_overdue ? <span className="text-danger">● en retard</span> : '') },
      ]}
    />
  );
}

export function GlobalDeliverablesList() {
  return (
    <GlobalList
      title="Tous les livrables"
      description="Livrables de tous les projets."
      queryKey="all-deliverables"
      fetcher={(id) => api.getProjectDeliverables(id)}
      empty="Aucun livrable."
      columns={[
        { key: 'name', label: 'Livrable', render: (r) => String(r.name ?? '') },
        { key: 'due_date', label: 'Échéance', render: (r) => (r.due_date ? new Date(String(r.due_date)).toLocaleDateString('fr-FR') : '—') },
        { key: 'status', label: 'Statut', render: (r) => status(r.status) },
      ]}
    />
  );
}

export function GlobalAlertsList() {
  const { data, isLoading } = useQuery({ queryKey: ['all-alerts'], queryFn: () => aggregate<Entity>(api.getProjectAlerts) });
  const rows = data ?? [];
  const flat: (Entity & { __project: Entity })[] = rows.flatMap((r) => r.items.map((item) => ({ ...item, __project: r.project })));
  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Toutes les alertes" description="Alertes calculées de tous les projets (dépassement, échéances…)." />
      <div className="flex flex-col gap-2">
        {isLoading && <p className="text-sm text-muted-foreground">Chargement…</p>}
        {!isLoading && flat.length === 0 && <p className="text-sm text-muted-foreground">Aucune alerte — tout est sous contrôle.</p>}
        {flat.map((a, i) => (
          <div key={i} className="flex items-center justify-between gap-3 rounded border border-border bg-surface px-3 py-2 text-sm">
            <Link href={`/projects/${a.__project.id}`} className="shrink-0 text-primary hover:underline">{String(a.__project.name ?? a.__project.code ?? '')}</Link>
            <span className="min-w-0 flex-1 truncate">{String(a.message ?? '')}</span>
            {status(a.severity)}
          </div>
        ))}
      </div>
    </div>
  );
}

export function GlobalResourcesList() {
  const { data, isLoading } = useQuery({ queryKey: ['all-resources'], queryFn: () => aggregate<Entity>(api.getProjectResources) });
  const rows = data ?? [];
  const flat: (Entity & { __project: Entity })[] = rows.flatMap((r) => r.items.map((item) => ({ ...item, __project: r.project })));
  const labels: Record<string, string> = { human: 'Humaine', financial: 'Financière', material: 'Matérielle', informational: 'Informationnelle' };
  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Ressources de tous les projets" description="Ressources allouées / consommées, projet par projet." />
      <DataTable
        columns={[
          { key: '__project', label: 'Projet', render: (r) => <Link href={`/projects/${(r as { __project: Entity }).__project.id}`} className="text-primary hover:underline">{String((r as { __project: Entity }).__project.name ?? '')}</Link> },
          { key: 'name', label: 'Ressource', render: (r) => String(r.name ?? '') },
          { key: 'type', label: 'Type', render: (r) => labels[String(r.type)] ?? String(r.type ?? '') },
          { key: 'amount', label: 'Réel / Planifié', render: (r) => `${r.actual_amount ?? '—'} / ${r.planned_amount ?? '—'}` },
          { key: 'is_over_budget', label: '', render: (r) => (r.is_over_budget ? <span className="text-danger">● dépassement</span> : '') },
        ]}
        rows={flat}
        loading={isLoading}
        emptyLabel="Aucune ressource."
      />
    </div>
  );
}

export function GlobalRisksList() {
  const { data, isLoading } = useQuery({ queryKey: ['all-risks'], queryFn: () => aggregate<Entity>(api.getProjectRisks) });
  const rows = data ?? [];
  const flat: (Entity & { __project: Entity })[] = rows.flatMap((r) => r.items.map((item) => ({ ...item, __project: r.project })));
  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Tous les risques" description="Registre des risques de tous les projets — triés par criticité." />
      <DataTable
        columns={[
          { key: '__project', label: 'Projet', render: (r) => <Link href={`/projects/${(r as { __project: Entity }).__project.id}`} className="text-primary hover:underline">{String((r as { __project: Entity }).__project.name ?? '')}</Link> },
          { key: 'title', label: 'Risque', render: (r) => String(r.title ?? '') },
          { key: 'category', label: 'Catégorie', render: (r) => api.RISK_CATEGORY_LABELS[String(r.category)] ?? String(r.category ?? '') },
          { key: 'criticality', label: 'Criticité', render: (r) => status(r.criticality) },
          { key: 'status', label: 'Statut', render: (r) => status(r.status) },
          { key: 'is_overdue', label: '', render: (r) => (r.is_overdue ? <span className="text-danger">● revue dépassée</span> : '') },
        ]}
        rows={flat}
        loading={isLoading}
        emptyLabel="Aucun risque."
      />
    </div>
  );
}

/* ------------------------------------------------------------------ */
/* Visualisations : Kanban et Gantt                                    */
/* ------------------------------------------------------------------ */

const PROJECT_STATUS: { value: string; label: string }[] = [
  { value: 'draft', label: 'Brouillon' },
  { value: 'active', label: 'En cours' },
  { value: 'on_hold', label: 'En pause' },
  { value: 'completed', label: 'Terminé' },
  { value: 'archived', label: 'Archivé' },
];

export function KanbanBoard() {
  const { data, isLoading } = useQuery({ queryKey: ['projects-board'], queryFn: () => api.projectsApi.list({ 'page.size': 200 } as never) });
  const projects = (data?.data ?? []) as Entity[];

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Tableau Kanban — Projets" description="Tous les projets regroupés par statut. Glissez mentalement vers l'étape suivante ; la modification se fait sur la fiche." />
      <div className="grid min-h-0 flex-1 auto-rows-fr grid-cols-1 gap-4 overflow-auto md:grid-cols-5">
        {PROJECT_STATUS.map((col) => {
          const items = projects.filter((p) => String(p.status) === col.value);
          return (
            <div key={col.value} className="flex min-h-0 flex-col rounded border border-border bg-surface">
              <div className="flex items-center justify-between border-b border-border px-3 py-2 text-sm font-semibold">
                <span>{col.label}</span>
                <span className="rounded bg-muted px-1.5 py-0.5 text-xs">{items.length}</span>
              </div>
              <div className="flex flex-1 flex-col gap-2 overflow-y-auto p-2">
                {isLoading && <p className="text-xs text-muted-foreground">Chargement…</p>}
                {!isLoading && items.length === 0 && <p className="text-xs text-muted-foreground">Aucun projet.</p>}
                {items.map((p) => (
                  <Link key={String(p.id)} href={`/projects/${p.id}`} className="rounded border border-border bg-background p-2 text-sm hover:bg-muted">
                    <div className="font-medium">{String(p.name ?? '')}</div>
                    <div className="mt-1 flex items-center justify-between text-xs text-muted-foreground">
                      <span className="font-mono">{String(p.code ?? '')}</span>
                      <span>{String(p.tasks_count ?? 0)} tâches</span>
                    </div>
                  </Link>
                ))}
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

export function GanttChart() {
  const { data, isLoading } = useQuery({ queryKey: ['projects-gantt'], queryFn: () => api.projectsApi.list({ 'page.size': 200 } as never) });
  const projects = ((data?.data ?? []) as Entity[]).filter((p) => p.start_date || p.end_date);
  const undated = ((data?.data ?? []) as Entity[]).filter((p) => !p.start_date && !p.end_date);

  const dates = projects.flatMap((p) => [new Date(String(p.start_date)), new Date(String(p.end_date))].filter((d) => !Number.isNaN(d.getTime())));
  const min = dates.length ? new Date(Math.min(...dates.map((d) => d.getTime()))) : new Date();
  const max = dates.length ? new Date(Math.max(...dates.map((d) => d.getTime()))) : new Date(min.getTime() + 1000 * 60 * 60 * 24 * 30);
  const range = Math.max(1, max.getTime() - min.getTime());

  function bar(p: Entity): { left: number; width: number } {
    const s = p.start_date ? new Date(String(p.start_date)).getTime() : min.getTime();
    const e = p.end_date ? new Date(String(p.end_date)).getTime() : s + 1000 * 60 * 60 * 24 * 14;
    const left = Math.max(0, ((s - min.getTime()) / range) * 100);
    const width = Math.min(100 - left, ((e - s) / range) * 100);
    return { left, width };
  }

  const months = 6;
  const monthStarts = Array.from({ length: months }, (_, i) => {
    const d = new Date(min);
    d.setMonth(min.getMonth() + i);
    return d;
  });

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Diagramme de Gantt — Projets" description="Plage de chaque projet (dates de début / fin) sur un calendrier." />
      <div className="min-h-0 flex-1 overflow-auto rounded border border-border bg-surface p-4">
        <div className="grid grid-cols-[minmax(220px,1fr)_2.5fr] gap-2">
          {/* En-têtes mois */}
          <div />
          <div className="relative h-6">
            {monthStarts.map((m, i) => (
              <span key={i} className="absolute -translate-x-1/2 text-xs text-muted-foreground" style={{ left: `${((m.getTime() - min.getTime()) / range) * 100}%` }}>
                {m.toLocaleDateString('fr-FR', { month: 'short' })}
              </span>
            ))}
          </div>

          {isLoading && <p className="text-sm text-muted-foreground">Chargement…</p>}
          {!isLoading && projects.map((p) => {
            const b = bar(p);
            return (
              <div key={String(p.id)} className="contents">
                <Link href={`/projects/${p.id}`} className="truncate self-center text-sm text-primary hover:underline">{String(p.name ?? '')}</Link>
                <div className="relative h-7 rounded bg-muted">
                  <div className="absolute top-1 h-5 rounded bg-primary" style={{ left: `${b.left}%`, width: `${Math.max(2, b.width)}%` }} title={String(p.name ?? '')} />
                </div>
              </div>
            );
          })}
        </div>
        {undated.length > 0 && (
          <p className="mt-3 text-xs text-muted-foreground">Sans dates : {undated.map((p) => p.name).join(', ')}</p>
        )}
      </div>
    </div>
  );
}
