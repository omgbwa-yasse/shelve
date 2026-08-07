import Link from 'next/link';

/**
 * Page 404 personnalisée.
 */
export default function NotFound() {
  return (
    <div className="flex min-h-screen items-center justify-center bg-surface">
      <div className="w-full max-w-md rounded-lg border border-border bg-background p-6 text-center shadow-sm">
        <h1 className="text-2xl font-semibold">404 — Page introuvable</h1>
        <p className="mt-2 text-sm text-muted-foreground">La ressource demandée n'existe pas ou n'est pas accessible.</p>
        <Link
          href="/"
          className="mt-4 inline-block rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90"
        >
          Retour à l'accueil
        </Link>
      </div>
    </div>
  );
}
