'use client';

/**
 * Rendu public des modules OPAC — moteur React du portail (R05 : le rendu
 * vit côté client, l'API ne fournit que la structure). Chaque module se rend
 * selon sa variante et ses réglages, surchargés par le breakpoint réel du
 * visiteur (`useBreakpoint`).
 */
import Link from 'next/link';
import { useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import type { Breakpoint, OpacBlock, OpacModule } from '../services/opac-builder.service';
import { listPublicNews, listPublicEvents, listPublicRecords } from '@/features/opac/services/opac.service';
import { effectiveSettings, blockHidden } from '../lib/builder';

const NAV_ITEMS: { href: string; label: string }[] = [
  { href: '/opac/search', label: 'Recherche' },
  { href: '/opac/records', label: 'Catalogue' },
  { href: '/opac/news', label: 'Actualités' },
  { href: '/opac/events', label: 'Événements' },
  { href: '/opac/pages', label: 'Informations' },
];

export function ModuleRenderer({
  module,
  block,
  breakpoint,
}: {
  module: OpacModule;
  block: OpacBlock;
  breakpoint: Breakpoint;
}) {
  if (blockHidden(block, breakpoint)) return null;
  const s = effectiveSettings(module, block, breakpoint);
  const variant = String(s.variant ?? module.variants[0]?.key ?? 'default');

  switch (module.key) {
    case 'menu':
      return <MenuModule title={String(s.title ?? '')} variant={variant} showSub={Boolean(s.show_sub)} />;
    case 'search':
      return <SearchModule placeholder={String(s.placeholder ?? '')} variant={variant} />;
    case 'news':
      return <NewsModule s={s} variant={variant} />;
    case 'events':
      return <EventsModule s={s} variant={variant} />;
    case 'calendar':
      return <CalendarModule s={s} />;
    case 'records':
    case 'document':
      return <RecordsModule s={s} variant={variant} digitalOnly={module.key === 'document'} />;
    case 'rss':
      return <RssModule s={s} />;
    case 'feedback':
      return <FeedbackModule s={s} variant={variant} />;
    case 'banner':
      return <BannerModule s={s} variant={variant} />;
    case 'text':
      return <TextModule s={s} />;
    case 'links':
      return <LinksModule s={s} />;
    default:
      return null;
  }
}

function ModuleShell({ title, children, className }: { title: string; children: React.ReactNode; className?: string }) {
  if (!title) return <>{children}</>;
  return (
    <section className={className}>
      <h2 className="mb-3 font-serif text-lg font-bold">{title}</h2>
      {children}
    </section>
  );
}

// ============================ MENU ============================

function MenuModule({ title, variant, showSub }: { title: string; variant: string; showSub: boolean }) {
  void showSub;
  if (variant === 'vertical') {
    return (
      <ModuleShell title={title}>
        <nav className="flex flex-col gap-1">
          {NAV_ITEMS.map((item) => (
            <Link key={item.href} href={item.href} className="rounded px-2 py-1.5 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              {item.label}
            </Link>
          ))}
        </nav>
      </ModuleShell>
    );
  }
  return (
    <ModuleShell title={title}>
      <details className="group relative">
        <summary className="flex cursor-pointer list-none items-center justify-between rounded border border-border px-3 py-2 text-sm font-medium">
          Menu
          <Icon name="chevronDown" className="h-4 w-4" />
        </summary>
        <nav className="mt-1 flex flex-col gap-1 rounded border border-border bg-surface p-1">
          {NAV_ITEMS.map((item) => (
            <Link key={item.href} href={item.href} className="rounded px-2 py-1.5 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              {item.label}
            </Link>
          ))}
        </nav>
      </details>
    </ModuleShell>
  );
}

// ============================ SEARCH ============================

function SearchModule({ placeholder, variant }: { placeholder: string; variant: string }) {
  const advanced = variant === 'advanced';
  return (
    <form
      action="/opac/search"
      className="flex w-full max-w-2xl items-center gap-2 rounded-lg border border-border bg-background p-1.5 shadow-sm"
    >
      <Icon name="search" className="ml-2 h-4 w-4 shrink-0 text-muted-foreground" />
      <input name="search" placeholder={placeholder} className="flex-1 bg-transparent text-sm outline-none focus:outline-none focus:ring-1 focus:ring-primary" />
      <button type="submit" className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
        Rechercher
      </button>
      {advanced && (
        <Link href="/opac/search/advanced" className="rounded px-3 py-2 text-xs text-muted-foreground hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
          Avancée
        </Link>
      )}
    </form>
  );
}

// ============================ NEWS ============================

function NewsModule({ s, variant }: { s: Record<string, unknown>; variant: string }) {
  const limit = Number(s.limit ?? 3);
  const cols = Number(s.cols ?? 3);
  const featuredOnly = Boolean(s.featured_only);
  const { data, isLoading } = useQuery({
    queryKey: ['opac-module-news', limit, featuredOnly],
    queryFn: () => listPublicNews({ per_page: limit, ...(featuredOnly ? { featured: true } : {}) }),
  });
  const items = data?.data ?? [];

  if (isLoading) return <Skeleton cols={cols} count={limit} />;
  if (items.length === 0) return null;

  if (variant === 'featured') {
    const [first, ...rest] = items;
    if (!first) return null;
    return (
      <ModuleShell title={String(s.title ?? '')}>
        <div className="space-y-3">
          <NewsCard item={first} large />
          <div className={`grid gap-3 sm:grid-cols-2`}>
            {rest.slice(0, Math.max(limit - 1, 0)).map((item) => (
              <NewsCard key={item.id} item={item} />
            ))}
          </div>
        </div>
      </ModuleShell>
    );
  }

  const grid = variant === 'list';
  return (
    <ModuleShell title={String(s.title ?? '')}>
      <div className={`grid gap-4 ${grid ? 'grid-cols-1' : ''}`} style={grid ? undefined : { gridTemplateColumns: `repeat(${cols}, minmax(0, 1fr))` }}>
        {items.map((item) => (
          <NewsCard key={item.id} item={item} />
        ))}
      </div>
    </ModuleShell>
  );
}

function NewsCard({ item, large }: { item: { id: number; title: string; summary?: string | null; image_url?: string | null; published_at?: string | null; created_at?: string | null; author?: { name: string } | null }; large?: boolean }) {
  const date = item.published_at ?? item.created_at;
  return (
    <article className={`flex flex-col rounded-xl border border-border bg-surface p-4 ${large ? 'lg:p-6' : ''}`}>
      {item.image_url && <img src={item.image_url} alt="" className="mb-3 h-36 w-full rounded-lg object-cover" />}
      {date && (
        <span className="mb-2 inline-flex w-fit rounded-full bg-primary px-2.5 py-0.5 text-xs font-medium text-primary-foreground">
          {new Date(date).toLocaleDateString('fr-FR')}
        </span>
      )}
      <h3 className={`font-serif font-semibold leading-snug ${large ? 'text-xl' : 'text-base'}`}>
        <Link href={`/opac/news/${item.id}`} className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
          {item.title}
        </Link>
      </h3>
      {item.summary && <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">{item.summary}</p>}
    </article>
  );
}

// ============================ EVENTS ============================

function EventsModule({ s, variant }: { s: Record<string, unknown>; variant: string }) {
  const limit = Number(s.limit ?? 4);
  const cols = Number(s.cols ?? 2);
  const filter = String(s.filter ?? 'upcoming');
  const { data, isLoading } = useQuery({
    queryKey: ['opac-module-events', limit, filter],
    queryFn: () => listPublicEvents({ per_page: limit, filter: filter as never }),
  });
  const items = data?.data ?? [];

  if (isLoading) return <Skeleton cols={cols} count={Math.min(limit, 4)} />;
  if (items.length === 0) return null;

  if (variant === 'upcoming' || variant === 'list') {
    return (
      <ModuleShell title={String(s.title ?? '')}>
        <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
          {items.map((item) => (
            <EventRow key={item.id} item={item} />
          ))}
        </ul>
      </ModuleShell>
    );
  }

  return (
    <ModuleShell title={String(s.title ?? '')}>
      <div className="grid gap-3" style={{ gridTemplateColumns: `repeat(${cols}, minmax(0, 1fr))` }}>
        {items.map((item) => (
          <EventRow key={item.id} item={item} />
        ))}
      </div>
    </ModuleShell>
  );
}

function EventRow({ item }: { item: { id: number; name: string; description?: string | null; start_date?: string | null; end_date?: string | null; location?: string | null } }) {
  const start = item.start_date ? new Date(item.start_date) : null;
  return (
    <li className="flex items-center gap-3 p-3">
      {start && (
        <div className="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-lg bg-primary/10 text-primary">
          <span className="text-xs font-bold uppercase">{start.toLocaleDateString('fr-FR', { month: 'short' })}</span>
          <span className="text-lg font-bold leading-none">{start.getDate()}</span>
        </div>
      )}
      <div className="min-w-0">
        <Link href={`/opac/events/${item.id}`} className="block truncate text-sm font-medium hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
          {item.name}
        </Link>
        {item.location && (
          <p className="flex items-center gap-1 truncate text-xs text-muted-foreground">
            <Icon name="mapPin" className="h-3 w-3 shrink-0" />
            {item.location}
          </p>
        )}
      </div>
    </li>
  );
}

// ============================ CALENDAR ============================

function CalendarModule({ s }: { s: Record<string, unknown> }) {
  const filter = String(s.filter ?? 'this_month');
  const { data } = useQuery({
    queryKey: ['opac-module-calendar', filter],
    queryFn: () => listPublicEvents({ per_page: 31, filter: filter as never }),
  });
  const items = data?.data ?? [];

  return (
    <ModuleShell title={String(s.title ?? '')}>
      {items.length === 0 ? (
        <p className="text-sm text-muted-foreground">Aucun événement sur la période.</p>
      ) : (
        <ul className="divide-y divide-border rounded-xl border border-border bg-surface">
          {items.map((item) => (
            <li key={item.id} className="flex items-center justify-between gap-2 p-2.5 text-sm">
              <span className="font-mono text-xs text-muted-foreground">
                {item.start_date ? new Date(item.start_date).toLocaleDateString('fr-FR') : '—'}
              </span>
              <Link href={`/opac/events/${item.id}`} className="flex-1 truncate text-left hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                {item.name}
              </Link>
            </li>
          ))}
        </ul>
      )}
    </ModuleShell>
  );
}

// ============================ RECORDS / DOCUMENTS ============================

function RecordsModule({ s, variant, digitalOnly }: { s: Record<string, unknown>; variant: string; digitalOnly: boolean }) {
  const limit = Number(s.limit ?? 4);
  const cols = Number(s.cols ?? 3);
  const { data, isLoading } = useQuery({
    queryKey: ['opac-module-records', limit, digitalOnly, variant],
    queryFn: () =>
      listPublicRecords({
        per_page: limit,
        ...(digitalOnly || Boolean(s.has_digital_copy) ? { has_digital_copy: true } : {}),
      }),
  });
  const items = data?.data ?? [];

  if (isLoading) return <Skeleton cols={cols} count={Math.min(limit, 6)} />;
  if (items.length === 0) return null;

  if (variant === 'featured') {
    const first = items[0];
    if (!first) return null;
    return (
      <ModuleShell title={String(s.title ?? '')}>
        <Link href={`/opac/records/${first.id}`} className="flex flex-col gap-3 rounded-xl border border-border bg-surface p-4 lg:flex-row focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
          <div className="flex h-40 w-full items-center justify-center rounded-lg bg-muted lg:h-auto lg:w-48">
            <Icon name="book" className="h-10 w-10 text-muted-foreground/50" />
          </div>
          <div>
            <h3 className="font-serif text-xl font-bold hover:text-primary">{first.title}</h3>
            {first.formatted_date_range && <p className="mt-1 text-sm text-muted-foreground">{first.formatted_date_range}</p>}
            {first.description && <p className="mt-2 line-clamp-2 text-sm text-muted-foreground">{first.description}</p>}
          </div>
        </Link>
      </ModuleShell>
    );
  }

  const grid = variant === 'list';
  return (
    <ModuleShell title={String(s.title ?? '')}>
      <div className={`grid gap-4 ${grid ? 'grid-cols-1' : ''}`} style={grid ? undefined : { gridTemplateColumns: `repeat(${cols}, minmax(0, 1fr))` }}>
        {items.map((item) => (
          <RecordCard key={item.id} item={item} />
        ))}
      </div>
    </ModuleShell>
  );
}

function RecordCard({ item }: { item: { id: number; title: string; code?: string | null; formatted_date_range?: string | null; description?: string | null } }) {
  return (
    <Link href={`/opac/records/${item.id}`} className="flex flex-col rounded-xl border border-border bg-surface p-4 transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
      <span className="mb-2 inline-flex w-fit items-center gap-1 rounded bg-muted px-2 py-0.5 font-mono text-[10px] text-muted-foreground">
        <Icon name="fileText" className="h-3 w-3" />
        {item.code ?? 'notice'}
      </span>
      <h3 className="font-serif text-base font-semibold leading-snug hover:text-primary">{item.title}</h3>
      {item.formatted_date_range && <p className="mt-1 text-xs text-muted-foreground">{item.formatted_date_range}</p>}
      {item.description && <p className="mt-1 line-clamp-2 text-xs text-muted-foreground">{item.description}</p>}
    </Link>
  );
}

// ============================ RSS ============================

function RssModule({ s }: { s: Record<string, unknown> }) {
  const url = String(s.url ?? '');
  return (
    <ModuleShell title={String(s.title ?? '')}>
      {url ? (
        <a href={url} target="_blank" rel="noreferrer" className="inline-flex items-center gap-2 rounded-lg border border-border px-3 py-2 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
          <Icon name="rss" className="h-4 w-4 text-primary" />
          {url}
        </a>
      ) : (
        <p className="text-sm text-muted-foreground">Configurez l’URL du flux RSS dans l’éditeur.</p>
      )}
    </ModuleShell>
  );
}

// ============================ FEEDBACK ============================

function FeedbackModule({ s, variant }: { s: Record<string, unknown>; variant: string }) {
  return (
    <ModuleShell title={String(s.title ?? '')}>
      <div className="rounded-xl border border-border bg-surface p-4">
        <p className="text-sm text-muted-foreground">Une question, un avis sur nos collections ?</p>
        <Link
          href={variant === 'form' ? '/opac/feedback' : '/opac/feedback'}
          className="mt-3 inline-flex items-center gap-1.5 rounded bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          <Icon name="star" className="h-4 w-4" />
          Nous écrire
        </Link>
      </div>
    </ModuleShell>
  );
}

// ============================ BANNER ============================

function BannerModule({ s, variant }: { s: Record<string, unknown>; variant: string }) {
  const image = String(s.image ?? '');
  const title = String(s.title ?? '');
  const link = String(s.link ?? '');
  const height = String(s.height ?? 'md');

  const content = (
    <div
      className={`flex flex-col justify-center rounded-xl border border-border bg-gradient-to-r from-primary to-primary/80 p-6 text-primary-foreground ${
        height === 'lg' ? 'min-h-64' : height === 'sm' ? 'min-h-24' : 'min-h-40'
      }`}
      style={image ? { backgroundImage: `url(${image})`, backgroundSize: 'cover', backgroundPosition: 'center' } : undefined}
    >
      {title && <h2 className={`font-serif font-bold ${height === 'lg' ? 'text-3xl' : 'text-xl'}`}>{title}</h2>}
      {variant === 'full' && !image && (
        <p className="mt-1 text-sm text-primary-foreground/85">Bannière du portail.</p>
      )}
    </div>
  );

  return link ? (
    <Link href={link} className="block hover:opacity-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70">
      {content}
    </Link>
  ) : (
    content
  );
}

// ============================ TEXT / LINKS ============================

function TextModule({ s }: { s: Record<string, unknown> }) {
  const html = String(s.html ?? '');
  const title = String(s.title ?? '');
  if (!html) return null;
  return (
    <ModuleShell title={title}>
      <div
        className="max-w-none text-sm leading-7 text-foreground [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-3 [&_h1]:mt-6 [&_h2]:mt-6 [&_h3]:mt-6 [&_h1]:font-serif [&_h2]:font-serif [&_h3]:font-serif [&_p]:mb-4 [&_a]:text-primary [&_ul]:mb-4 [&_ol]:mb-4 [&_img]:my-4 [&_img]:rounded-lg [&_table]:my-4 [&_th]:bg-muted [&_th]:p-2 [&_th]:font-semibold [&_td]:border [&_td]:border-border [&_td]:p-2"
        dangerouslySetInnerHTML={{ __html: html }}
      />
    </ModuleShell>
  );
}

function LinksModule({ s }: { s: Record<string, unknown> }) {
  const title = String(s.title ?? '');
  const links = useMemo(() => {
    return String(s.links ?? '')
      .split('\n')
      .map((line) => line.trim())
      .filter(Boolean)
      .map((line) => {
        const parts = line.split('|');
        const label = (parts[0] ?? line).trim();
        const url = (parts.slice(1).join('|') || label).trim();
        return { label, url };
      });
  }, [s.links]);

  if (links.length === 0) return null;

  return (
    <ModuleShell title={title}>
      <ul className="space-y-1.5">
        {links.map((link, i) => (
          <li key={i}>
            <Link href={link.url} className="inline-flex items-center gap-1.5 text-sm hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              <Icon name="arrowRightCircle" className="h-3.5 w-3.5" />
              {link.label}
            </Link>
          </li>
        ))}
      </ul>
    </ModuleShell>
  );
}

function Skeleton({ cols, count }: { cols: number; count: number }) {
  void cols;
  return (
    <div className="grid gap-4" style={{ gridTemplateColumns: `repeat(${cols}, minmax(0, 1fr))` }}>
      {Array.from({ length: count }).map((_, i) => (
        <div key={i} className="h-36 animate-pulse rounded-xl border border-border bg-surface" />
      ))}
    </div>
  );
}
