/**
 * État de chargement global (Suspense) pendant les transitions de routes.
 */
export default function Loading() {
  return (
    <div className="flex h-full min-h-[40vh] items-center justify-center">
      <div className="flex items-center gap-3 text-sm text-muted-foreground">
        <span className="h-4 w-4 animate-spin rounded-full border-2 border-border border-t-primary" />
        Chargement…
      </div>
    </div>
  );
}
