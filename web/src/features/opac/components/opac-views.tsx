import Link from 'next/link';
import { Icon } from '@/components/icons';
import { formatDate } from '@/utils/format-date';
import type { PublicRecord } from '../services/opac.service';

/**
 * Carte de notice — reproduit la carte `opac-card` des vues OPAC
 * (titre, référence, dates ISAD(G), créateur, disponibilité).
 */
export function RecordCard({ record }: { record: PublicRecord }) {
  const isad = record.isad_fields;
  return (
    <article className="flex h-full flex-col rounded-xl border border-border bg-surface shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
      <div className="flex flex-1 flex-col gap-3 p-4">
        <div className="flex items-start justify-between gap-2">
          <span className="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
            <Icon name="book" className="h-3 w-3" />
            {isad?.level_of_description ?? record.type ?? 'Notice'}
          </span>
          {record.digital_copy_available ? (
            <span className="inline-flex items-center gap-1 rounded-full bg-success/10 px-2 py-0.5 text-xs font-medium text-success">
              <Icon name="checkCircle" className="h-3 w-3" />
              Disponible
            </span>
          ) : (
            <span className="rounded-full bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground">
              Indisponible
            </span>
          )}
        </div>

        <div className="min-w-0">
          <Link href={`/opac/records/${record.id}`} className="font-serif text-base font-semibold leading-snug text-foreground hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            {record.title}
          </Link>
          {record.code && (
            <p className="mt-0.5 text-xs text-muted-foreground">
              <span className="font-mono">{record.code}</span>
            </p>
          )}
        </div>

        {record.formatted_date_range && (
          <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
            <Icon name="calendar" className="h-3 w-3" />
            {record.formatted_date_range}
          </p>
        )}

        {isad?.creator && (
          <p className="text-xs text-muted-foreground">
            <span className="font-medium text-foreground">Créateur :</span> {isad.creator}
          </p>
        )}

        {record.description && (
          <p className="line-clamp-3 text-sm text-muted-foreground">{record.description}</p>
        )}
      </div>

      <div className="border-t border-border px-4 py-2">
        <Link href={`/opac/records/${record.id}`} className="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
          Consulter
          <Icon name="arrowRightCircle" className="h-3.5 w-3.5" />
        </Link>
      </div>
    </article>
  );
}

/** État vide commun aux listes OPAC. */
export function EmptyState({ title, description, action }: { title: string; description: string; action?: { href: string; label: string } }) {
  return (
    <div className="rounded-xl border border-border bg-surface py-12 text-center">
      <Icon name="search" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/30" />
      <h3 className="text-base font-semibold">{title}</h3>
      <p className="mx-auto mt-1 max-w-md text-sm text-muted-foreground">{description}</p>
      {action && (
        <Link href={action.href} className="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
          <Icon name="search" className="h-4 w-4" />
          {action.label}
        </Link>
      )}
    </div>
  );
}

/** Pagination simple (enveloppe records `pagination`). */
export function PaginationBar({
  page,
  lastPage,
  total,
  onChange,
}: {
  page: number;
  lastPage: number;
  total: number;
  onChange: (page: number) => void;
}) {
  if (lastPage <= 1) return null;
  return (
    <div className="flex items-center justify-between text-sm text-muted-foreground">
      <span>
        Page {page} sur {lastPage} ({total} résultat{total > 1 ? 's' : ''})
      </span>
      <div className="flex gap-2">
        <button
          type="button"
          disabled={page <= 1}
          onClick={() => onChange(page - 1)}
          className="rounded-lg border border-border bg-surface px-3 py-1.5 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          Précédent
        </button>
        <button
          type="button"
          disabled={page >= lastPage}
          onClick={() => onChange(page + 1)}
          className="rounded-lg border border-border bg-surface px-3 py-1.5 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          Suivant
        </button>
      </div>
    </div>
  );
}

/** Date courte au format OPAC (ex. 12/08/2026). */
export function OpacDate({ value }: { value: string | null | undefined }) {
  if (!value) return null;
  return <time dateTime={value}>{formatDate(value)}</time>;
}
