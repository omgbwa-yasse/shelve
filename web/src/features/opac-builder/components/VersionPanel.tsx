'use client';

import { Icon } from '@/components/icons';
import type { OpacPageVersion } from '../services/opac-builder.service';

const STATUS_BADGE: Record<string, { label: string; className: string }> = {
  draft: { label: 'Brouillon', className: 'bg-muted text-muted-foreground' },
  published: { label: 'Publiée', className: 'bg-green-100 text-green-700' },
  archived: { label: 'Archivée', className: 'bg-amber-100 text-amber-700' },
};

/**
 * Gestion des versions d'une page — cycle de vie : édition d'un brouillon,
 * publication (unique version active), duplication (branche) et restauration.
 */
export function VersionPanel({
  versions,
  currentId,
  busy,
  onSwitch,
  onCreate,
  onActivate,
  onDuplicate,
  onRestore,
  onDelete,
}: {
  versions: OpacPageVersion[];
  currentId: number | null;
  busy: boolean;
  onSwitch: (id: number) => void;
  onCreate: () => void;
  onActivate: (id: number) => void;
  onDuplicate: (id: number) => void;
  onRestore: (id: number) => void;
  onDelete: (id: number) => void;
}) {
  return (
    <div className="flex flex-col gap-3">
      <div className="flex items-center justify-between">
        <h3 className="text-sm font-semibold">Versions ({versions.length})</h3>
        <button
          type="button"
          disabled={busy}
          onClick={onCreate}
          className="inline-flex items-center gap-1 rounded border border-primary/30 bg-primary/5 px-2 py-1 text-xs font-medium text-primary hover:bg-primary/10 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          <Icon name="plus" className="h-3 w-3" />
          Nouvelle version
        </button>
      </div>

      <div className="space-y-2">
        {versions.map((version) => {
          const badge =
            STATUS_BADGE[version.status] ??
            ({ label: 'Brouillon', className: 'bg-muted text-muted-foreground' } as const);
          const isCurrent = version.id === currentId;

          return (
            <div
              key={version.id}
              className={`rounded-lg border bg-surface p-3 ${isCurrent ? 'border-primary ring-1 ring-primary' : 'border-border'}`}
            >
              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={() => onSwitch(version.id)}
                  className="flex-1 text-left text-sm font-medium hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                >
                  {version.name}
                </button>
                {version.is_active && (
                  <span className="rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-semibold text-primary">
                    active
                  </span>
                )}
                <span className={`rounded px-1.5 py-0.5 text-[10px] font-semibold ${badge.className}`}>
                  {badge.label}
                </span>
              </div>

              {version.change_description && (
                <p className="mt-1 text-xs text-muted-foreground">{version.change_description}</p>
              )}
              <p className="mt-1 text-[11px] text-muted-foreground">
                {version.created_by ? `${version.created_by} · ` : ''}
                {version.created_at ? new Date(version.created_at).toLocaleString('fr-FR') : ''}
                {version.blocks ? ` · ${version.blocks.length} bloc(s)` : ''}
              </p>

              <div className="mt-2 flex flex-wrap gap-1.5 border-t border-border pt-2">
                {!version.is_active && (
                  <button
                    type="button"
                    disabled={busy}
                    onClick={() => onActivate(version.id)}
                    className="rounded bg-primary px-2 py-1 text-xs font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                  >
                    {version.status === 'draft' ? 'Publier' : 'Republier'}
                  </button>
                )}
                <button
                  type="button"
                  disabled={busy}
                  onClick={() => onDuplicate(version.id)}
                  className="rounded border border-border px-2 py-1 text-xs hover:bg-muted disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                >
                  Dupliquer
                </button>
                {!version.is_active && version.status !== 'draft' && (
                  <button
                    type="button"
                    disabled={busy}
                    onClick={() => onRestore(version.id)}
                    className="rounded border border-border px-2 py-1 text-xs hover:bg-muted disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                  >
                    Restaurer
                  </button>
                )}
                {version.status === 'draft' && !version.is_active && (
                  <button
                    type="button"
                    disabled={busy}
                    onClick={() => onDelete(version.id)}
                    className="rounded border border-danger/30 px-2 py-1 text-xs text-danger hover:bg-danger/10 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                  >
                    Supprimer
                  </button>
                )}
              </div>
            </div>
          );
        })}
        {versions.length === 0 && (
          <p className="py-4 text-center text-xs text-muted-foreground">Aucune version.</p>
        )}
      </div>
    </div>
  );
}
