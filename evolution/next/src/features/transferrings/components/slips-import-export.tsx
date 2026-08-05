'use client';

import { PageHeader } from '@/components/ui/page';

/** Import Excel / EAD / SEDA d'un bordereau de versement. */
export function SlipsImport() {
  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Import de bordereaux" description="Import Excel / EAD / SEDA d'un bordereau de versement." />
      <form className="flex max-w-xl flex-col gap-3 rounded border border-border bg-surface p-4">
        <label className="text-sm font-medium">Fichier</label>
        <input type="file" className="rounded border border-border bg-background px-3 py-2 text-sm" />
        <p className="text-xs text-muted-foreground">Formats acceptés : .xlsx, .xls, .xml (EAD/SEDA). L'import est asynchrone.</p>
        <div>
          <button type="submit" className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90">Importer</button>
        </div>
      </form>
    </div>
  );
}

/** Export SEDA / EAD / Excel des bordereaux. */
export function SlipsExport() {
  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Export de bordereaux" description="Export SEDA / EAD / Excel des bordereaux." />
      <div className="grid max-w-xl grid-cols-1 gap-3">
        {['Export Excel (.xlsx)', 'Export SEDA 2.1 (.xml)', 'Export EAD (.xml)'].map((f) => (
          <button key={f} type="button" className="rounded border border-border bg-surface px-4 py-3 text-left text-sm hover:bg-muted">
            {f}
          </button>
        ))}
      </div>
    </div>
  );
}
