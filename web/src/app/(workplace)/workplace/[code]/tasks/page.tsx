'use client';

import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useWorkplace } from '@/features/workplaces/context';
import { listWorkplaceTasks } from '@/features/workplaces/services/workplace.service';
import { Icon } from '@/components/icons';
import { formatDate } from '@/utils/format-date';
import type { WorkplaceTask } from '@/features/workplaces/types';

const STATUS_LABELS: Record<string, string> = {
  pending: 'En attente',
  in_progress: 'En cours',
  completed: 'Terminée',
  cancelled: 'Annulée',
};

const PRIORITY_COLORS: Record<string, string> = {
  high: '#dc2626',
  normal: '#2563eb',
  low: '#6b7280',
};

/**
 * Tâches rattachées au workplace (site tasks, équivalent Alfresco Site Tasks) —
 * liste des tâches du site avec filtres par statut et par assignation.
 */
export default function WorkplaceTasksPage() {
  const { code, workplace } = useWorkplace();
  const [status, setStatus] = useState('');
  const [assignedTo, setAssignedTo] = useState('');

  const { data, isLoading } = useQuery({
    queryKey: ['workplace-tasks', code, status, assignedTo],
    queryFn: () =>
      listWorkplaceTasks(code, {
        status: status || undefined,
        assigned_to: assignedTo || undefined,
      }),
    enabled: code.length > 0,
  });

  const tasks = data?.data ?? [];

  const isMember = (workplace?.members ?? []).some((m) => m.user_id === workplace?.owner_id);

  return (
    <div className="flex flex-col gap-4">
      <div className="flex flex-wrap items-end gap-3 rounded-xl border border-border bg-surface p-3 shadow-sm">
        <div className="flex items-center gap-2">
          <Icon name="listCheck" className="h-5 w-5 text-green-600" />
          <h3 className="font-semibold">Tâches du site</h3>
        </div>
        <label className="ml-auto flex flex-col gap-1 text-xs font-semibold text-muted-foreground">
          Statut
          <select value={status} onChange={(e) => setStatus(e.target.value)} className="rounded border border-border bg-background px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
            <option value="">Tous les statuts</option>
            {Object.entries(STATUS_LABELS).map(([value, label]) => (
              <option key={value} value={value}>{label}</option>
            ))}
          </select>
        </label>
        <label className="flex flex-col gap-1 text-xs font-semibold text-muted-foreground">
          Assignation
          <select value={assignedTo} onChange={(e) => setAssignedTo(e.target.value)} className="rounded border border-border bg-background px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
            <option value="">Toutes</option>
            <option value="me">Mes tâches</option>
          </select>
        </label>
      </div>

      {isLoading ? (
        <p className="px-4 py-10 text-center text-sm text-muted-foreground">Chargement…</p>
      ) : tasks.length === 0 ? (
        <div className="px-4 py-10 text-center">
          <Icon name="listCheck" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/20" />
          <p className="text-sm font-medium text-muted-foreground">Aucune tâche dans cet espace</p>
        </div>
      ) : (
        <ul className="divide-y divide-border overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
          {tasks.map((task: WorkplaceTask) => (
            <li key={task.id} className="flex items-start gap-3 px-4 py-3 hover:bg-muted/50">
              <span className="mt-1 flex h-9 w-9 items-center justify-center rounded-full bg-green-50">
                <Icon name="listCheck" className="h-4 w-4 text-green-600" />
              </span>
              <div className="min-w-0 flex-1">
                <div className="flex items-start justify-between gap-3">
                  <div className="min-w-0">
                    <p className="text-sm font-medium">{task.title}</p>
                    {task.description && (
                      <p className="mt-0.5 truncate text-sm text-muted-foreground">{task.description}</p>
                    )}
                  </div>
                  <span
                    className="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium text-white"
                    style={{ background: PRIORITY_COLORS[task.priority] ?? '#6b7280' }}
                  >
                    {task.priority}
                  </span>
                </div>
                <div className="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                  <span className="rounded-full bg-muted px-2 py-0.5">
                    {STATUS_LABELS[task.status] ?? task.status}
                  </span>
                  {task.due_date && (
                    <span className="flex items-center gap-1">
                      <Icon name="calendar" className="h-3 w-3" />
                      {formatDate(task.due_date)}
                    </span>
                  )}
                  <span>{task.assignee?.name ?? 'Non assignée'}</span>
                </div>
              </div>
            </li>
          ))}
        </ul>
      )}

      {!isMember && (
        <p className="text-center text-xs text-muted-foreground">
          Affichage en lecture seule — seuls les gestionnaires peuvent créer des tâches.
        </p>
      )}
    </div>
  );
}
