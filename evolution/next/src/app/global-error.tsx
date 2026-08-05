'use client';

/**
 * Gestion des erreurs globales de l'application (root error boundary).
 */
export default function GlobalError({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  return (
    <html lang="fr">
      <body>
        <div className="flex min-h-screen items-center justify-center bg-surface">
          <div className="w-full max-w-md rounded-lg border border-danger/40 bg-background p-6 text-center shadow-sm">
            <h1 className="text-lg font-semibold text-danger">Une erreur est survenue</h1>
            <p className="mt-2 text-sm text-muted-foreground">{error.message}</p>
            <button
              type="button"
              onClick={reset}
              className="mt-4 rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90"
            >
              Réessayer
            </button>
          </div>
        </div>
      </body>
    </html>
  );
}
