'use client';

import { useQuery } from '@tanstack/react-query';
import { PageHeader, InfoPanel, InfoScreen } from '@/components/ui/page';
import { thesaurusConceptsApi } from '../services/tool.service';

/**
 * Vues spécialisées Thésaurus (feature Outils) : hiérarchie, recherche,
 * import/export.
 */
export function ThesaurusViews({ view }: { view: 'hierarchy' | 'search' | 'export-import' }) {
  const { data } = useQuery({
    queryKey: ['thesaurus-concepts'],
    queryFn: async () => (await thesaurusConceptsApi.list({ per_page: 200 })) as { data: any[] },
  });
  const concepts = data?.data ?? [];

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader
        title={view === 'hierarchy' ? 'Hiérarchie des termes' : view === 'search' ? 'Recherche thésaurus' : 'Import / Export thésaurus'}
        description={`${concepts.length} termes — ` + (view === 'export-import' ? 'les exports SKOS-RDF/CSV/JSON sont générés côté Laravel.' : 'navigation alphabétique.')}
      />
      {view === 'export-import' ? (
        <InfoPanel title="Formats" items={[['Export', 'SKOS-RDF, CSV, JSON (côté Laravel)'], ['Import', 'POST /api/v1/thesaurus/import']]} />
      ) : (
        <div className="rounded border border-border bg-surface p-4">
          {view === 'search' && (
            <input type="search" placeholder="Rechercher un terme…" className="mb-3 w-64 rounded border border-border bg-background px-3 py-1.5 text-sm" />
          )}
          <ul className="divide-y divide-border text-sm">
            {concepts.slice(0, 100).map((c) => (
              <li key={c.id} className="py-2">{String(c.preferred_label ?? c.name ?? '—')}</li>
            ))}
          </ul>
        </div>
      )}
    </div>
  );
}

/** Générateur de code-barres (rendu binaire côté Laravel). */
export function Barcode() {
  return (
    <InfoScreen
      title="Générateur de code-barres"
      description="Génération de codes-barres (milon/barcode)."
      sections={[
        ['En attente', 'Le rendu binaire (image PNG) est émis par Laravel sans endpoint API JSON ; le module Next affichera un aperçu une fois le contrat exposé.'],
      ]}
    />
  );
}
