'use client';

import { useQuery } from '@tanstack/react-query';
import { PageHeader, StatCard } from '@/components/ui/page';
import { tasksApi } from '../services/workflow.service';

/**
 * Tableau de bord Workflow — échéances, retards, taux de respect des échéances
 * calculés sur données réelles (API tasks).
 */
export function WorkflowDashboard() {
  const { data, isPending } = useQuery({
    queryKey: ['wf-dashboard'],
    queryFn: async () => (await tasksApi.list({ per_page: 200 })) as { data: any[]; meta?: any },
  });

  const tasks = data?.data ?? [];
  const pending = tasks.filter((t) => t.status === 'pending' || t.status === 'in_progress');
  const overdue = pending.filter((t) => (t.due_date ? new Date(t.due_date) < new Date() : false));
  const completedOnTime = tasks.filter((t) => t.status === 'completed' && t.due_date && t.completed_at && new Date(t.completed_at) <= new Date(t.due_date));
  const completedWithDue = tasks.filter((t) => t.status === 'completed' && t.due_date);
  const rate = completedWithDue.length ? Math.round((completedOnTime.length / completedWithDue.length) * 100) : null;

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Tableau de bord Workflow" description="Échéances, retards et taux de respect — calculés sur données réelles." />
      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        <StatCard label="Tâches de workflow" value={isPending ? '…' : tasks.length} />
        <StatCard label="En attente / En cours" value={isPending ? '…' : pending.length} accent="warn" />
        <StatCard label="En retard" value={isPending ? '…' : overdue.length} accent="danger" />
        <StatCard label="Respect des échéances" value={rate === null ? '—' : `${rate}%`} accent="ok" />
      </div>
      <div className="rounded border border-border bg-surface p-4">
        <h2 className="mb-2 text-sm font-semibold">Tâches en retard</h2>
        {overdue.length === 0 ? (
          <p className="text-sm text-muted-foreground">Aucune tâche en retard.</p>
        ) : (
          <ul className="divide-y divide-border text-sm">
            {overdue.slice(0, 20).map((t) => (
              <li key={t.id} className="flex items-center justify-between py-2">
                <span>{t.title}</span>
                <span className="text-danger">{t.due_date ? new Date(t.due_date).toLocaleDateString('fr-FR') : '—'}</span>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
