'use client';

import { useParams } from 'next/navigation';
import Link from 'next/link';
import { useQuery } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import { getPublicRecord } from '@/features/opac/services/opac.service';
import { OpacDate } from '@/features/opac/components/opac-views';

/** Ligne clé/valeur d'une fiche détail. */
function Field({ label, children }: { label: string; children?: React.ReactNode }) {
  return (
    <div>
      <dt className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{label}</dt>
      <dd className="mt-0.5 text-sm">{children ?? '—'}</dd>
    </div>
  );
}

export default function OpacRecordShowPage() {
  const params = useParams<{ id: string }>();
  const id = String(params.id);

  const { data, isLoading, isError } = useQuery({
    queryKey: ['opac-record', id],
    queryFn: () => getPublicRecord(id),
  });

  const record = data?.data;

  return (
    <div className="mx-auto max-w-6xl px-4 py-8">
      <nav className="mb-6 text-sm text-muted-foreground">
        <Link href="/opac/search" className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Accueil</Link>
        <span className="mx-2">›</span>
        <Link href="/opac/records" className="hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Catalogue</Link>
        <span className="mx-2">›</span>
        <span className="text-foreground">{record?.title ?? 'Notice'}</span>
      </nav>

      {isLoading ? (
        <div className="h-96 animate-pulse rounded-xl border border-border bg-surface" />
      ) : isError || !record ? (
        <div className="rounded-xl border border-border bg-surface py-16 text-center">
          <Icon name="book" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/30" />
          <h2 className="text-base font-semibold">Notice indisponible</h2>
          <p className="mt-1 text-sm text-muted-foreground">
            Cette notice est expirée, non publiée ou n&apos;existe pas.
          </p>
          <Link href="/opac/search" className="mt-4 inline-block rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
            Retour à la recherche
          </Link>
        </div>
      ) : (
        <div className="grid gap-6 lg:grid-cols-4">
          {/* Colonne disponibilité */}
          <aside className="lg:col-span-1">
            <div className="rounded-xl border border-border bg-surface p-5 text-center">
              <div className="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-lg bg-muted">
                <Icon name="book" className="h-10 w-10 text-muted-foreground" />
              </div>
              {record.digital_copy_available ? (
                <p className="inline-flex items-center gap-1.5 rounded-lg bg-success/10 px-3 py-1.5 text-sm font-medium text-success">
                  <Icon name="checkCircle" className="h-4 w-4" />
                  Disponible
                </p>
              ) : (
                <p className="inline-flex items-center gap-1.5 rounded-lg bg-muted px-3 py-1.5 text-sm font-medium text-muted-foreground">
                  <Icon name="clock" className="h-4 w-4" />
                  Indisponible
                </p>
              )}
              {record.publisher && (
                <p className="mt-3 text-xs text-muted-foreground">Publié par {record.publisher.name}</p>
              )}
            </div>

            {record.thesaurus_concepts.length > 0 && (
              <div className="mt-4 rounded-xl border border-border bg-surface p-5">
                <h2 className="mb-3 text-sm font-semibold">Mots-clés</h2>
                <div className="flex flex-wrap gap-1.5">
                  {record.thesaurus_concepts.map((c) => (
                    <span key={c.id} className="rounded-full border border-border bg-background px-2.5 py-1 text-xs">
                      {c.term}
                    </span>
                  ))}
                </div>
              </div>
            )}
          </aside>

          {/* Détail ISAD(G) */}
          <div className="lg:col-span-3">
            <div className="rounded-xl border border-border bg-surface p-6 shadow-sm">
              <div className="mb-1 inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">
                <Icon name="book" className="h-3 w-3" />
                {record.isad_fields?.level_of_description ?? record.type ?? 'Notice'}
              </div>
              <h1 className="mt-3 font-serif text-2xl font-bold">{record.title}</h1>
              {record.subtitle && <p className="mt-1 text-muted-foreground">{record.subtitle}</p>}
              {record.code && (
                <p className="mt-1 font-mono text-xs text-muted-foreground">{record.code}</p>
              )}

              {/* Description */}
              {record.description && (
                <div className="mt-6">
                  <h2 className="mb-2 border-b pb-1 text-sm font-semibold">Description</h2>
                  <p className="whitespace-pre-line text-sm leading-relaxed">{record.description}</p>
                </div>
              )}

              {/* Champs ISAD(G) essentiels */}
              <div className="mt-6 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                <Field label="Cote / référence (3.1.1)">{record.isad_fields?.reference_code}</Field>
                <Field label="Dates (3.1.3)">
                  {record.formatted_date_range ??
                    (record.isad_fields?.dates?.exact ||
                      [record.isad_fields?.dates?.start, record.isad_fields?.dates?.end].filter(Boolean).join(' — '))}
                </Field>
                <Field label="Niveau de description (3.1.4)">{record.isad_fields?.level_of_description}</Field>
                <Field label="Importance matérielle (3.1.5)">{record.isad_fields?.extent}</Field>
                <Field label="Producteur (3.2.1)">{record.isad_fields?.creator}</Field>
              </div>

              {/* Champs détaillés */}
              {record.location && <Field label="Localisation d'origine">{record.location}</Field>}
              {record.classification && <Field label="Classification">{record.classification}</Field>}
              {record.series && <Field label="Série">{record.series}</Field>}
              {record.format && <Field label="Support">{record.format}</Field>}
              {record.dimensions && <Field label="Dimensions">{record.dimensions}</Field>}
              {record.language && <Field label="Langue">{record.language}</Field>}
              {record.access_conditions && (
                <div className="mt-6">
                  <h2 className="mb-2 border-b pb-1 text-sm font-semibold">Conditions d&apos;accès</h2>
                  <p className="text-sm">{record.access_conditions}</p>
                </div>
              )}
              {record.reproduction_conditions && (
                <div className="mt-4">
                  <h2 className="mb-2 border-b pb-1 text-sm font-semibold">Conditions de reproduction</h2>
                  <p className="text-sm">{record.reproduction_conditions}</p>
                </div>
              )}
              {record.archival_history && (
                <div className="mt-4">
                  <h2 className="mb-2 border-b pb-1 text-sm font-semibold">Historique de la conservation</h2>
                  <p className="text-sm">{record.archival_history}</p>
                </div>
              )}
              {record.note && (
                <div className="mt-4">
                  <h2 className="mb-2 border-b pb-1 text-sm font-semibold">Note</h2>
                  <p className="text-sm">{record.note}</p>
                </div>
              )}

              <footer className="mt-8 flex flex-wrap items-center justify-between gap-3 border-t pt-4">
                <small className="text-muted-foreground">
                  <Icon name="clock" className="mr-1 inline h-3 w-3" />
                  Dernière mise à jour : <OpacDate value={record.updated_at} />
                </small>
                <Link href="/opac/records" className="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                  <Icon name="arrowLeftSquare" className="h-4 w-4" />
                  Retour au catalogue
                </Link>
              </footer>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
