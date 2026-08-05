import type { ResourceConfig } from '@/lib/crud/types';

/**
 * Écran de repli affiché lorsqu'aucune config CRUD ne couvre le chemin demandé.
 * Sert de porte d'entrée pour les écrans « action » sans CRUD (dashboards,
 * imports/exports, comptes…).
 */
export function FallbackScreen({ config, path }: { config?: ResourceConfig; path: string }) {
  return (
    <div className="flex h-full flex-col items-start gap-4">
      <header>
        <h1 className="text-xl font-semibold">Écran non configuré</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Le chemin <code className="rounded bg-muted px-1.5 py-0.5">{path}</code> est déclaré dans la
          navigation mais n'a pas encore de config de ressource dans{' '}
          <code className="rounded bg-muted px-1.5 py-0.5">lib/crud/registry.ts</code>.
        </p>
      </header>
      <div className="rounded border border-border bg-surface p-4 text-sm text-muted-foreground">
        {config ? (
          <p>Ressource proche trouvée : {config.plural}. Ajoutez un alias ou une config dédiée.</p>
        ) : (
          <p>Ajoutez une <code>ResourceConfig</code> pour ce chemin pour rendre cet écran.</p>
        )}
      </div>
    </div>
  );
}
