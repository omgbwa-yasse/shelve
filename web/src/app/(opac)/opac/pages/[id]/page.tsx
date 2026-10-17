'use client';

import { useParams } from 'next/navigation';
import Link from 'next/link';
import { useQuery } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import { getPublicPage, listPublicPages } from '@/features/opac/services/opac.service';
import { OpacDate } from '@/features/opac/components/opac-views';

export default function OpacPageShowPage() {
  const params = useParams<{ id: string }>();
  const id = String(params.id);

  const { data, isLoading, isError } = useQuery({
    queryKey: ['opac-page', id],
    queryFn: () => getPublicPage(id),
  });
  const { data: related } = useQuery({
    queryKey: ['opac-pages-related', id],
    queryFn: () => listPublicPages({ per_page: 3 }),
  });

  const page = data?.data;
  const relatedItems = (related?.data ?? []).filter((p) => p.id !== page?.id).slice(0, 3);

  if (isLoading) {
    return <div className="mx-auto max-w-3xl px-4 py-12"><div className="h-96 animate-pulse rounded-xl border border-border bg-surface" /></div>;
  }

  if (isError || !page) {
    return (
      <div className="mx-auto max-w-3xl px-4 py-16 text-center">
        <Icon name="fileText" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/30" />
        <h1 className="text-lg font-semibold">Page indisponible</h1>
        <p className="mt-1 text-sm text-muted-foreground">Cette page n&apos;existe pas ou n&apos;est pas publiée.</p>
        <Link href="/opac/pages" className="mt-4 inline-block rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Retour aux pages</Link>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-3xl px-4 py-8">
      <nav className="mb-6 text-sm text-muted-foreground">
        <Link href="/opac/search" className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Accueil</Link>
        <span className="mx-2">›</span>
        <Link href="/opac/pages" className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Pages</Link>
        <span className="mx-2">›</span>
        <span className="text-foreground">{page.title}</span>
      </nav>

      <article className="rounded-xl border border-border bg-surface p-6 shadow-sm md:p-10">
        <header className="mb-6">
          <h1 className="font-serif text-3xl font-bold leading-tight">{page.title}</h1>
          {page.meta_description && <p className="mt-3 text-muted-foreground">{page.meta_description}</p>}
          <p className="mt-3 flex items-center gap-1.5 text-sm text-muted-foreground">
            <Icon name="calendar" className="h-4 w-4" />
            Dernière mise à jour : <OpacDate value={page.updated_at} />
          </p>
        </header>

        {page.content && (
          <div
            className="max-w-none leading-7 text-foreground [&_h1]:mt-6 [&_h2]:mt-6 [&_h3]:mt-6 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-3 [&_h1]:font-serif [&_h2]:font-serif [&_h3]:font-serif [&_p]:mb-4 [&_a]:text-primary [&_ul]:mb-4 [&_ol]:mb-4 [&_img]:my-4 [&_img]:rounded-lg [&_table]:my-4 [&_th]:bg-muted [&_th]:p-2 [&_th]:font-semibold [&_td]:border [&_td]:border-border [&_td]:p-2"
            dangerouslySetInnerHTML={{ __html: page.content }}
          />
        )}

        <footer className="mt-8 flex flex-wrap items-center justify-between gap-3 border-t pt-4">
          <small className="text-muted-foreground">
            <Icon name="clock" className="mr-1 inline h-3 w-3" />
            Créée le <OpacDate value={page.created_at} />
          </small>
          <Link href="/opac/pages" className="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            <Icon name="arrowLeftSquare" className="h-4 w-4" />
            Retour aux pages
          </Link>
        </footer>
      </article>

      {relatedItems.length > 0 && (
        <div className="mt-8">
          <h2 className="mb-4 font-serif text-lg font-semibold">Autres pages</h2>
          <div className="grid gap-4 sm:grid-cols-3">
            {relatedItems.map((p) => (
              <Link key={p.id} href={`/opac/pages/${p.id}`} className="rounded-xl border border-border bg-surface p-4 transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                <h3 className="text-sm font-semibold leading-snug hover:text-primary">{p.title}</h3>
                {p.meta_description && <p className="mt-1 line-clamp-2 text-xs text-muted-foreground">{p.meta_description}</p>}
              </Link>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
