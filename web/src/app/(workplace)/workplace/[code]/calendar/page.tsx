'use client';

import { useMemo, useState } from 'react';
import Link from 'next/link';
import { useQuery } from '@tanstack/react-query';
import { useWorkplace } from '@/features/workplaces/context';
import { getWorkplaceCalendar, type WorkplaceCalendarEvent } from '@/features/workplaces/services/workplace.service';
import { Icon } from '@/components/icons';
import clsx from 'clsx';

const WEEKDAYS = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
const MONTHS = [
  'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
  'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
];

const TYPE_LABELS: Record<WorkplaceCalendarEvent['type'], string> = {
  project_start: 'Début de projet',
  project_end: 'Fin de projet',
  milestone: 'Jalon',
  task_due: 'Tâche',
};

const DEFAULT_COLOR = '#2563eb';

function toDateKey(d: Date): string {
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

/**
 * Calendrier du workplace — vue mensuelle des éléments datés (début/fin des
 * projets rattachés, jalons et tâches à échéance), alimentée par
 * `GET /api/v1/workplaces/{code}/calendar`.
 */
export default function WorkplaceCalendarPage() {
  const { code } = useWorkplace();
  const [cursor, setCursor] = useState(() => new Date());

  const { data, isLoading, isError } = useQuery({
    queryKey: ['workplace-calendar', code],
    queryFn: () => getWorkplaceCalendar(code),
    enabled: code.length > 0,
  });

  const events = data?.data ?? [];

  const eventsByDate = useMemo(() => {
    const map = new Map<string, WorkplaceCalendarEvent[]>();
    for (const ev of events) {
      const list = map.get(ev.date) ?? [];
      list.push(ev);
      map.set(ev.date, list);
    }
    return map;
  }, [events]);

  const year = cursor.getFullYear();
  const month = cursor.getMonth();

  const days: Date[] = useMemo(() => {
    const first = new Date(year, month, 1);
    const gridStart = startOfWeekMonday(first);
    return Array.from({ length: 42 }, (_, i) => {
      const d = new Date(gridStart);
      d.setDate(gridStart.getDate() + i);
      return d;
    });
  }, [year, month]);

  const todayKey = toDateKey(new Date());

  const monthLabel = `${MONTHS[month]} ${year}`;

  const prevMonth = () => setCursor((c) => new Date(c.getFullYear(), c.getMonth() - 1, 1));
  const nextMonth = () => setCursor((c) => new Date(c.getFullYear(), c.getMonth() + 1, 1));
  const goToday = () => setCursor(new Date());

  return (
    <div className="flex flex-col gap-4">
      <header className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h3 className="flex items-center gap-2 text-lg font-semibold">
            <Icon name="calendar" className="h-5 w-5 text-muted-foreground" />
            Calendrier
          </h3>
          <p className="text-sm text-muted-foreground">
            Projets, jalons et tâches rattachés à cet espace ({events.length} événement(s)).
          </p>
        </div>
        <div className="flex items-center gap-2">
          <button
            type="button"
            onClick={prevMonth}
            className="rounded border border-border bg-surface px-2.5 py-1.5 text-sm hover:bg-muted"
            aria-label="Mois précédent"
          >
            ‹
          </button>
          <span className="min-w-40 text-center text-sm font-semibold">{monthLabel}</span>
          <button
            type="button"
            onClick={nextMonth}
            className="rounded border border-border bg-surface px-2.5 py-1.5 text-sm hover:bg-muted"
            aria-label="Mois suivant"
          >
            ›
          </button>
          <button
            type="button"
            onClick={goToday}
            className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground hover:opacity-90"
          >
            Aujourd'hui
          </button>
        </div>
      </header>

      {/* ===================== LÉGENDE ===================== */}
      <div className="flex flex-wrap gap-4 text-xs text-muted-foreground">
        <span className="flex items-center gap-1.5"><span className="h-2.5 w-2.5 rounded-full bg-blue-600" /> Début / fin de projet</span>
        <span className="flex items-center gap-1.5"><span className="h-2.5 w-2.5 rounded-full bg-emerald-600" /> Jalon</span>
        <span className="flex items-center gap-1.5"><span className="h-2.5 w-2.5 rounded-full bg-amber-600" /> Tâche</span>
      </div>

      {isLoading ? (
        <p className="py-12 text-center text-sm text-muted-foreground">Chargement…</p>
      ) : isError ? (
        <p className="py-12 text-center text-sm text-muted-foreground">Impossible de charger le calendrier.</p>
      ) : (
        <div className="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
          <div className="grid grid-cols-7 border-b border-border bg-muted/50 text-center text-[11px] font-bold uppercase tracking-wide text-muted-foreground">
            {WEEKDAYS.map((w) => (
              <div key={w} className="px-1 py-2">{w}</div>
            ))}
          </div>
          <div className="grid grid-cols-7">
            {days.map((day) => {
              const key = toDateKey(day);
              const dayEvents = eventsByDate.get(key) ?? [];
              const inMonth = day.getMonth() === month;
              const isToday = key === todayKey;
              return (
                <div
                  key={key}
                  className={clsx(
                    'flex min-h-24 flex-col gap-1 border-b border-r border-border p-1.5',
                    !inMonth && 'bg-muted/40',
                  )}
                >
                  <span
                    className={clsx(
                      'flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium',
                      isToday ? 'bg-primary text-primary-foreground' : 'text-foreground',
                      !inMonth && 'text-muted-foreground/60',
                    )}
                  >
                    {day.getDate()}
                  </span>
                  <div className="flex flex-col gap-1 overflow-hidden">
                    {dayEvents.slice(0, 3).map((ev, i) => (
                      <Link
                        key={`${ev.date}-${i}`}
                        href={ev.project_id ? `/projects/${ev.project_id}` : '#'}
                        title={`${TYPE_LABELS[ev.type]} — ${ev.title}`}
                        className="truncate rounded px-1.5 py-0.5 text-[10px] font-medium text-white hover:opacity-85"
                        style={{ background: ev.color ?? DEFAULT_COLOR }}
                      >
                        {ev.title}
                      </Link>
                    ))}
                    {dayEvents.length > 3 && (
                      <span className="px-1 text-[10px] font-semibold text-muted-foreground">
                        +{dayEvents.length - 3} autres
                      </span>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}

      {/* ===================== LISTE DU MOIS ===================== */}
      {events.length > 0 && (
        <section className="rounded-xl border border-border bg-surface shadow-sm">
          <header className="border-b border-border px-4 py-2.5 text-sm font-semibold">
            Événements du mois
          </header>
          <ul className="divide-y divide-border">
            {events
              .filter((ev) => ev.date.startsWith(`${year}-${String(month + 1).padStart(2, '0')}`))
              .map((ev, i) => (
                <li key={`${ev.date}-${i}`} className="flex items-center gap-3 px-4 py-2 text-sm hover:bg-muted/50">
                  <span className="w-2.5 shrink-0 self-stretch rounded-full" style={{ background: ev.color ?? DEFAULT_COLOR }} />
                  <span className="w-24 shrink-0 font-mono text-xs text-muted-foreground">{ev.date}</span>
                  <div className="min-w-0 flex-1">
                    <p className="truncate font-medium">{ev.title}</p>
                    {ev.subtitle && <p className="truncate text-xs text-muted-foreground">{ev.subtitle}</p>}
                  </div>
                  <span className="shrink-0 rounded-full bg-muted px-2 py-0.5 text-[10px] font-medium text-muted-foreground">
                    {TYPE_LABELS[ev.type]}
                  </span>
                  {ev.project_id && (
                    <Link href={`/projects/${ev.project_id}`} className="shrink-0 rounded border border-border px-2 py-1 text-xs hover:bg-muted">
                      Ouvrir
                    </Link>
                  )}
                </li>
              ))}
          </ul>
        </section>
      )}
    </div>
  );
}

/** Lundi comme premier jour de la semaine (convention française). */
function startOfWeekMonday(date: Date): Date {
  const d = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  const diff = (d.getDay() + 6) % 7;
  d.setDate(d.getDate() - diff);
  return d;
}
