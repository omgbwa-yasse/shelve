'use client';

import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useWorkplace } from '@/features/workplaces/context';
import { listActivities } from '@/features/workplaces/services/workplace.service';
import { Icon } from '@/components/icons';
import { formatDate } from '@/utils/format-date';
import type { WorkplaceActivity } from '@/features/workplaces/types';

const ACTIVITY_TYPES: Record<string, string> = {
  shared_folder: 'Dossier partagé',
  shared_document: 'Document partagé',
  created_folder: 'Dossier créé',
  created_document: 'Document créé',
  deleted_folder: 'Dossier retiré',
  deleted_document: 'Document retiré',
  member_added: 'Membre ajouté',
  member_removed: 'Membre retiré',
  settings_changed: 'Paramètres modifiés',
};

const TYPE_CONFIG: Record<string, { icon: 'folderPlus' | 'fileText' | 'folderOpen' | 'personPlus' | 'personCheck' | 'settings'; bg: string; color: string }> = {
  shared_folder: { icon: 'folderPlus', bg: '#d1fae5', color: '#059669' },
  shared_document: { icon: 'fileText', bg: '#dbeafe', color: '#2563eb' },
  created_folder: { icon: 'folderPlus', bg: '#d1fae5', color: '#059669' },
  created_document: { icon: 'fileText', bg: '#dbeafe', color: '#2563eb' },
  deleted_folder: { icon: 'folderOpen', bg: '#fee2e2', color: '#dc2626' },
  deleted_document: { icon: 'fileText', bg: '#fee2e2', color: '#dc2626' },
  member_added: { icon: 'personPlus', bg: '#e0e7ff', color: '#4f46e5' },
  member_removed: { icon: 'personCheck', bg: '#fef3c7', color: '#d97706' },
  settings_changed: { icon: 'settings', bg: '#f3f4f6', color: '#6b7280' },
};

/**
 * Historique des activités — reproduit `workplaces/activities/index.blade.php`
 * (filtres type/membre/période + frise chronologique).
 */
export default function WorkplaceActivitiesPage() {
  const { code, workplace } = useWorkplace();
  const [type, setType] = useState('');
  const [userId, setUserId] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');
  const [page, setPage] = useState(1);

  const { data, isLoading } = useQuery({
    queryKey: ['workplace-activities', code, type, userId, dateFrom, dateTo, page],
    queryFn: () =>
      listActivities(code, {
        'filter[activity_type]': type || undefined,
        'filter[user_id]': userId || undefined,
        'filter[created_at][gte]': dateFrom || undefined,
        'filter[created_at][lte]': dateTo ? `${dateTo}T23:59:59` : undefined,
        page,
        'page.size': 25,
        sort: '-created_at',
      }),
    enabled: code.length > 0,
  });

  const activities = data?.data ?? [];
  const meta = data?.meta;
  const hasPrev = Boolean(data?.links?.prev);
  const hasNext = Boolean(data?.links?.next);
  const members = workplace?.members ?? [];

  const resetFilters = () => {
    setType('');
    setUserId('');
    setDateFrom('');
    setDateTo('');
    setPage(1);
  };

  return (
    <div className="flex flex-col gap-4">
      {/* ===================== FILTRES ===================== */}
      <form
        onSubmit={(e) => {
          e.preventDefault();
          setPage(1);
        }}
        className="flex flex-wrap items-end gap-3 rounded-xl border border-border bg-surface p-3 shadow-sm"
      >
        <label className="flex flex-col gap-1 text-xs font-semibold text-muted-foreground">
          Type d'activité
          <select value={type} onChange={(e) => setType(e.target.value)} className="rounded border border-border bg-background px-2 py-1 text-sm">
            <option value="">Toutes les activités</option>
            {Object.entries(ACTIVITY_TYPES).map(([value, label]) => (
              <option key={value} value={value}>{label}</option>
            ))}
          </select>
        </label>
        <label className="flex flex-col gap-1 text-xs font-semibold text-muted-foreground">
          Du
          <input type="date" value={dateFrom} onChange={(e) => setDateFrom(e.target.value)} className="rounded border border-border bg-background px-2 py-1 text-sm" />
        </label>
        <label className="flex flex-col gap-1 text-xs font-semibold text-muted-foreground">
          Au
          <input type="date" value={dateTo} onChange={(e) => setDateTo(e.target.value)} className="rounded border border-border bg-background px-2 py-1 text-sm" />
        </label>
        <label className="flex flex-col gap-1 text-xs font-semibold text-muted-foreground">
          Membre
          <select value={userId} onChange={(e) => setUserId(e.target.value)} className="rounded border border-border bg-background px-2 py-1 text-sm">
            <option value="">Tous les membres</option>
            {members.map((m) => (
              <option key={m.id} value={m.user_id}>{m.user?.name ?? `#${m.user_id}`}</option>
            ))}
          </select>
        </label>
        <div className="flex gap-2">
          <button type="submit" className="flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">
            <Icon name="search" className="h-3.5 w-3.5" />
            Filtrer
          </button>
          {(type || userId || dateFrom || dateTo) && (
            <button type="button" onClick={resetFilters} className="rounded border border-border px-2.5 py-1.5 text-sm hover:bg-muted">
              <Icon name="close" className="h-3.5 w-3.5" />
            </button>
          )}
        </div>
      </form>

      {/* ===================== FRISE ===================== */}
      <section className="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
        <header className="flex items-center gap-2 border-b border-border px-4 py-2.5 text-sm font-semibold">
          <Icon name="history" className="h-4 w-4 text-green-600" />
          Historique des activités
          <span className="rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">{meta?.total ?? 0}</span>
        </header>
        {isLoading ? (
          <p className="px-4 py-10 text-center text-sm text-muted-foreground">Chargement…</p>
        ) : activities.length === 0 ? (
          <div className="px-4 py-10 text-center">
            <Icon name="history" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/20" />
            <p className="text-sm font-medium text-muted-foreground">Aucune activité enregistrée</p>
            <p className="text-xs text-muted-foreground">Les actions effectuées dans cet espace apparaîtront ici.</p>
          </div>
        ) : (
          <ul className="divide-y divide-border">
            {activities.map((a: WorkplaceActivity, index) => (
              <ActivityItem key={a.id} activity={a} last={index === activities.length - 1} />
            ))}
          </ul>
        )}
      </section>

      {/* ===================== PAGINATION ===================== */}
      {meta && meta.last_page > 1 && (
        <footer className="flex items-center justify-between text-sm text-muted-foreground">
          <span>
            Page {meta.current_page} sur {meta.last_page} ({meta.total} activité(s))
          </span>
          <div className="flex gap-2">
            <button
              type="button"
              disabled={!hasPrev}
              onClick={() => setPage((p) => Math.max(1, p - 1))}
              className="rounded border border-border px-2.5 py-1 disabled:opacity-40"
            >
              Précédent
            </button>
            <button
              type="button"
              disabled={!hasNext}
              onClick={() => setPage((p) => Math.min(meta.last_page, p + 1))}
              className="rounded border border-border px-2.5 py-1 disabled:opacity-40"
            >
              Suivant
            </button>
          </div>
        </footer>
      )}
    </div>
  );
}

function ActivityItem({ activity, last }: { activity: WorkplaceActivity; last: boolean }) {
  const cfg = TYPE_CONFIG[activity.activity_type];
  return (
    <li className="flex items-start gap-3 px-4 py-3 hover:bg-muted/50">
      <div className="relative mt-1">
        <span className="flex h-9 w-9 items-center justify-center rounded-full" style={{ background: cfg?.bg ?? '#f3f4f6' }}>
          <span style={{ color: cfg?.color ?? '#6b7280' }}>
            <Icon name={cfg?.icon ?? 'settings'} className="h-4 w-4" />
          </span>
        </span>
        {!last && <span className="absolute left-1/2 top-9 h-[calc(100%+0.5rem)] w-px -translate-x-1/2 bg-border" />}
      </div>
      <div className="min-w-0 flex-1">
        <div className="flex items-start justify-between gap-3">
          <p className="text-sm">
            <strong className="font-semibold">{activity.user?.name ?? 'Système'}</strong>{' '}
            <span className="text-muted-foreground">{activity.description}</span>
          </p>
          <span className="shrink-0 text-xs text-muted-foreground">{formatDate(activity.created_at)}</span>
        </div>
        {cfg && (
          <span className="mt-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-medium" style={{ background: cfg.bg, color: cfg.color }}>
            {ACTIVITY_TYPES[activity.activity_type] ?? activity.activity_type}
          </span>
        )}
      </div>
    </li>
  );
}
