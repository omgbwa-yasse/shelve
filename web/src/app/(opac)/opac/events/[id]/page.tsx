'use client';

import { useParams } from 'next/navigation';
import Link from 'next/link';
import { useQuery } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import { getPublicEvent, listPublicEvents } from '@/features/opac/services/opac.service';

function formatLongDate(value: string): string {
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
}

function eventStatus(start: string | null, end: string | null): { label: string; tone: 'upcoming' | 'ongoing' | 'past' } {
  if (!start) return { label: 'Passé', tone: 'past' };
  const now = Date.now();
  const s = new Date(start).getTime();
  const e = end ? new Date(end).getTime() : s;
  if (s > now) return { label: 'À venir', tone: 'upcoming' };
  if (e >= now) return { label: 'En cours', tone: 'ongoing' };
  return { label: 'Passé', tone: 'past' };
}

const STATUS_STYLE: Record<string, string> = {
  upcoming: 'bg-success/10 text-success',
  ongoing: 'bg-warning/10 text-warning',
  past: 'bg-muted text-muted-foreground',
};

export default function OpacEventShowPage() {
  const params = useParams<{ id: string }>();
  const id = String(params.id);

  const { data, isLoading, isError } = useQuery({
    queryKey: ['opac-event', id],
    queryFn: () => getPublicEvent(id),
  });
  const { data: related } = useQuery({
    queryKey: ['opac-events-related', id],
    queryFn: () => listPublicEvents({ filter: 'upcoming', per_page: 3 }),
  });

  const event = data?.data;
  const status = event ? eventStatus(event.start_date, event.end_date) : null;
  const relatedItems = (related?.data ?? []).filter((e) => e.id !== event?.id).slice(0, 3);

  if (isLoading) {
    return <div className="mx-auto max-w-3xl px-4 py-12"><div className="h-96 animate-pulse rounded-xl border border-border bg-surface" /></div>;
  }

  if (isError || !event) {
    return (
      <div className="mx-auto max-w-3xl px-4 py-16 text-center">
        <Icon name="calendar" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/30" />
        <h1 className="text-lg font-semibold">Événement indisponible</h1>
        <p className="mt-1 text-sm text-muted-foreground">Cet événement n&apos;existe pas ou a été retiré.</p>
        <Link href="/opac/events" className="mt-4 inline-block rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Retour aux événements</Link>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-3xl px-4 py-8">
      <nav className="mb-6 text-sm text-muted-foreground">
        <Link href="/opac/search" className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Accueil</Link>
        <span className="mx-2">›</span>
        <Link href="/opac/events" className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Événements</Link>
        <span className="mx-2">›</span>
        <span className="text-foreground">{event.name}</span>
      </nav>

      <article className="rounded-xl border border-border bg-surface p-6 shadow-sm md:p-8">
        <header className="mb-5">
          <div className="mb-3 flex flex-wrap items-center gap-2">
            <span className="inline-flex items-center rounded-full bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground">
              <Icon name="calendar" className="mr-1 h-3 w-3" />
              {formatLongDate(event.start_date ?? '')}
            </span>
            {event.end_date && event.end_date !== event.start_date && (
              <span className="inline-flex items-center rounded-full bg-muted px-3 py-1.5 text-xs text-muted-foreground">
                au {formatLongDate(event.end_date)}
              </span>
            )}
            {status && (
              <span className={`inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium ${STATUS_STYLE[status.tone]}`}>
                {status.label}
              </span>
            )}
          </div>

          <h1 className="font-serif text-2xl font-bold">{event.name}</h1>

          <div className="mt-3 grid gap-2 text-sm text-muted-foreground sm:grid-cols-2">
            {event.location && (
              <p className="flex items-center gap-2">
                <Icon name="bookmark" className="h-4 w-4" />
                {event.location}
              </p>
            )}
            {event.start_date && (
              <p className="flex items-center gap-2">
                <Icon name="clock" className="h-4 w-4" />
                {formatLongDate(event.start_date)} à{' '}
                {new Date(event.start_date).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
              </p>
            )}
            {event.is_online && event.online_link && (
              <p className="flex items-center gap-2">
                <Icon name="globe" className="h-4 w-4" />
                <a href={event.online_link} target="_blank" rel="noreferrer" className="text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                  Événement en ligne
                </a>
              </p>
            )}
          </div>
        </header>

        {event.description && (
          <div
            className="max-w-none leading-7 text-foreground [&_h1]:mt-5 [&_h2]:mt-5 [&_h3]:mt-5 [&_h1]:mb-2 [&_h2]:mb-2 [&_h3]:mb-2 [&_h1]:font-serif [&_h2]:font-serif [&_h3]:font-serif [&_p]:mb-3 [&_a]:text-primary [&_ul]:mb-3 [&_ol]:mb-3 [&_img]:my-4 [&_img]:rounded-lg"
            dangerouslySetInnerHTML={{ __html: event.description }}
          />
        )}

        <footer className="mt-8 flex flex-wrap items-center justify-between gap-3 border-t pt-4">
          <small className="text-muted-foreground">
            <Icon name="clock" className="mr-1 inline h-3 w-3" />
            Dernière mise à jour : {event.updated_at ? new Date(event.updated_at).toLocaleString('fr-FR') : '—'}
          </small>
          <Link href="/opac/events" className="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            <Icon name="arrowLeftSquare" className="h-4 w-4" />
            Retour aux événements
          </Link>
        </footer>
      </article>

      {relatedItems.length > 0 && (
        <div className="mt-8">
          <h2 className="mb-4 font-serif text-lg font-semibold">Autres événements à venir</h2>
          <div className="grid gap-4 sm:grid-cols-3">
            {relatedItems.map((e) => (
              <Link key={e.id} href={`/opac/events/${e.id}`} className="rounded-xl border border-border bg-surface p-4 transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                <span className="text-xs text-muted-foreground">{formatLongDate(e.start_date ?? '')}</span>
                <h3 className="mt-1.5 text-sm font-semibold leading-snug hover:text-primary">{e.name}</h3>
                {e.location && <p className="mt-1 text-xs text-muted-foreground">{e.location}</p>}
              </Link>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
