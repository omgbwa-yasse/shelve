'use client';

import { useParams } from 'next/navigation';
import Link from 'next/link';
import { useQuery } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import { getPublicNews, listPublicNews } from '@/features/opac/services/opac.service';
import { OpacDate } from '@/features/opac/components/opac-views';

export default function OpacNewsShowPage() {
  const params = useParams<{ id: string }>();
  const id = String(params.id);

  const { data, isLoading, isError } = useQuery({
    queryKey: ['opac-news', id],
    queryFn: () => getPublicNews(id),
  });
  const { data: related } = useQuery({
    queryKey: ['opac-news-related', id],
    queryFn: () => listPublicNews({ per_page: 3 }),
  });

  const article = data?.data;
  const relatedItems = (related?.data ?? []).filter((n) => n.id !== article?.id).slice(0, 3);

  if (isLoading) {
    return <div className="mx-auto max-w-3xl px-4 py-12"><div className="h-96 animate-pulse rounded-xl border border-border bg-surface" /></div>;
  }

  if (isError || !article) {
    return (
      <div className="mx-auto max-w-3xl px-4 py-16 text-center">
        <Icon name="newspaper" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/30" />
        <h1 className="text-lg font-semibold">Article indisponible</h1>
        <p className="mt-1 text-sm text-muted-foreground">Cet article n&apos;existe pas ou n&apos;est pas publié.</p>
        <Link href="/opac/news" className="mt-4 inline-block rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Retour aux actualités</Link>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-3xl px-4 py-8">
      <nav className="mb-6 text-sm text-muted-foreground">
        <Link href="/opac/search" className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Accueil</Link>
        <span className="mx-2">›</span>
        <Link href="/opac/news" className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Actualités</Link>
        <span className="mx-2">›</span>
        <span className="text-foreground">{article.title}</span>
      </nav>

      <article className="rounded-xl border border-border bg-surface p-6 shadow-sm md:p-10">
        <header className="mb-6">
          <span className="inline-flex items-center rounded-full bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground">
            <OpacDate value={article.published_at ?? article.created_at} />
          </span>
          <h1 className="mt-4 font-serif text-3xl font-bold leading-tight">{article.title}</h1>
          {article.summary && <p className="mt-3 text-lg text-muted-foreground">{article.summary}</p>}
          <div className="mt-4 flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
            {article.author && (
              <span className="inline-flex items-center gap-1.5">
                <Icon name="person" className="h-4 w-4" />
                {article.author.name}
              </span>
            )}
            <span className="inline-flex items-center gap-1.5">
              <Icon name="calendar" className="h-4 w-4" />
              Publié le <OpacDate value={article.published_at ?? article.created_at} />
            </span>
          </div>
        </header>

        {article.content && (
          <div
            className="max-w-none leading-7 text-foreground [&_h1]:mt-6 [&_h2]:mt-6 [&_h3]:mt-6 [&_h1]:mb-3 [&_h2]:mb-3 [&_h3]:mb-3 [&_h1]:font-serif [&_h2]:font-serif [&_h3]:font-serif [&_p]:mb-4 [&_a]:text-primary [&_ul]:mb-4 [&_ol]:mb-4 [&_img]:my-4 [&_img]:rounded-lg"
            dangerouslySetInnerHTML={{ __html: article.content }}
          />
        )}

        <footer className="mt-8 flex flex-wrap items-center justify-between gap-3 border-t pt-4">
          <small className="text-muted-foreground">
            <Icon name="clock" className="mr-1 inline h-3 w-3" />
            Dernière mise à jour : <OpacDate value={article.updated_at} />
          </small>
          <Link href="/opac/news" className="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            <Icon name="arrowLeftSquare" className="h-4 w-4" />
            Retour aux actualités
          </Link>
        </footer>
      </article>

      {relatedItems.length > 0 && (
        <div className="mt-8">
          <h2 className="mb-4 font-serif text-lg font-semibold">Autres actualités</h2>
          <div className="grid gap-4 sm:grid-cols-3">
            {relatedItems.map((n) => (
              <Link key={n.id} href={`/opac/news/${n.id}`} className="rounded-xl border border-border bg-surface p-4 transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                <span className="text-xs text-muted-foreground"><OpacDate value={n.published_at ?? n.created_at} /></span>
                <h3 className="mt-1.5 text-sm font-semibold leading-snug hover:text-primary">{n.title}</h3>
                {n.summary && <p className="mt-1 line-clamp-2 text-xs text-muted-foreground">{n.summary}</p>}
              </Link>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
