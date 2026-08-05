import { cn } from '@/utils/cn';

/** Colonne d'un tableau — rendu fourni par l'écran (la feature), pas par un moteur. */
export type TableColumn<T> = {
  key: string;
  label: string;
  render?: (row: T) => React.ReactNode;
  className?: string;
  sortable?: boolean;
  onSort?: (key: string) => void;
  sortDir?: 'asc' | 'desc';
};

/**
 * Tableau de données présentatif (atome UI). Chaque feature construit ses
 * colonnes et appelle ce composant — aucun moteur CRUD.
 */
export function DataTable<T extends object>({
  columns,
  rows,
  loading = false,
  error = false,
  emptyLabel = 'Aucun résultat.',
  actions,
}: {
  columns: TableColumn<T>[];
  rows: T[];
  loading?: boolean;
  error?: boolean;
  emptyLabel?: string;
  actions?: (row: T) => React.ReactNode;
}) {
  return (
    <div className="min-h-0 flex-1 overflow-auto rounded border border-border">
      <table className="w-full text-left text-sm">
        <thead className="sticky top-0 bg-surface">
          <tr>
            {columns.map((col) => (
              <th
                key={col.key}
                onClick={col.sortable && col.onSort ? () => col.onSort?.(col.key) : undefined}
                className={cn(
                  'px-3 py-2 font-medium text-muted-foreground',
                  col.className,
                  col.sortable && col.onSort ? 'cursor-pointer select-none hover:text-foreground' : '',
                )}
              >
                {col.label}
                {col.sortDir && <span className="ml-1">{col.sortDir === 'desc' ? '↓' : '↑'}</span>}
              </th>
            ))}
            {actions && <th className="px-3 py-2" />}
          </tr>
        </thead>
        <tbody>
          {loading && (
            <tr><td colSpan={columns.length + (actions ? 1 : 0)} className="px-3 py-8 text-center text-muted-foreground">Chargement…</td></tr>
          )}
          {!loading && error && (
            <tr><td colSpan={columns.length + (actions ? 1 : 0)} className="px-3 py-8 text-center text-danger">Erreur de chargement</td></tr>
          )}
          {!loading && !error && rows.map((row, index) => (
            <tr key={String((row as { id?: string | number }).id ?? index)} className="border-t border-border/60 hover:bg-muted">
              {columns.map((col) => (
                <td key={col.key} className={cn('px-3 py-2', col.className)}>
                  {col.render ? col.render(row) : String((row as Record<string, unknown>)[col.key] ?? '—')}
                </td>
              ))}
              {actions && <td className="px-3 py-2 text-right">{actions(row)}</td>}
            </tr>
          ))}
          {!loading && !error && rows.length === 0 && (
            <tr><td colSpan={columns.length + (actions ? 1 : 0)} className="px-3 py-8 text-center text-muted-foreground">{emptyLabel}</td></tr>
          )}
        </tbody>
      </table>
    </div>
  );
}

export function Pagination({ page, totalPages, total, onChange }: { page: number; totalPages: number; total?: number; onChange: (p: number) => void }) {
  if (totalPages <= 1) {
    return total !== undefined ? <span className="text-sm text-muted-foreground">{total} résultat(s)</span> : null;
  }
  return (
    <div className="flex items-center justify-between text-sm">
      {total !== undefined && <span className="text-muted-foreground">{total} résultat(s)</span>}
      <div className="flex gap-2">
        <button type="button" disabled={page <= 1} onClick={() => onChange(page - 1)} className="rounded border border-border px-2 py-1 disabled:opacity-40">Précédent</button>
        <span className="px-2 py-1">Page {page} / {totalPages}</span>
        <button type="button" disabled={page >= totalPages} onClick={() => onChange(page + 1)} className="rounded border border-border px-2 py-1 disabled:opacity-40">Suivant</button>
      </div>
    </div>
  );
}
