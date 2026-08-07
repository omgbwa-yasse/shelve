'use client';

import { useRef, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useWorkplace } from '@/features/workplaces/context';
import {
  listWorkplaceDocuments,
  createWorkplaceFolder,
  uploadWorkplaceDocument,
  deleteWorkplaceDocument,
  shareWorkplaceDocument,
  unshareWorkplaceDocument,
  transferWorkplaceDocument,
  listClassificationActivities,
  downloadWorkplaceDocumentUrl,
} from '@/features/workplaces/services/workplace.service';
import { Icon } from '@/components/icons';
import { formatDate } from '@/utils/format-date';
import type { WorkplaceDocument } from '@/features/workplaces/types';

type Crumb = { id: number | null; name: string };

type ActivityItem = { id: number; code?: string; name: string; parent_id?: number | null };

/**
 * Bibliothèque Documents d'un workplace : navigation par fil d'Ariane, création
 * de dossiers, upload, téléchargement, suppression, **partage** vers le module
 * Records et **transfert** vers Records (avec classe du plan de classement).
 *
 * Règle d'accès : un document du workplace n'est visible du module Records que
 * s'il est partagé ; le transfert le sort du workplace (`workplace_id = null`).
 */
export default function WorkplaceDocumentsPage() {
  const { code } = useWorkplace();
  const queryClient = useQueryClient();
  const [path, setPath] = useState<Crumb[]>([{ id: null, name: 'Documents' }]);
  const [folderName, setFolderName] = useState('');
  const [showFolderForm, setShowFolderForm] = useState(false);
  const [transferTarget, setTransferTarget] = useState<WorkplaceDocument | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const currentParent = path[path.length - 1]?.id ?? null;

  const queryKey = ['workplace-documents', code, currentParent];
  const { data, isLoading, isError } = useQuery({
    queryKey,
    queryFn: () => listWorkplaceDocuments(code, currentParent),
    enabled: code.length > 0,
  });
  const items = data?.data ?? [];

  const invalidate = () => queryClient.invalidateQueries({ queryKey: ['workplace-documents', code] });

  const createFolder = useMutation({
    mutationFn: () => createWorkplaceFolder(code, { name: folderName, parent_id: currentParent }),
    onSuccess: () => {
      invalidate();
      setFolderName('');
      setShowFolderForm(false);
    },
  });

  const upload = useMutation({
    mutationFn: (file: File) => uploadWorkplaceDocument(code, file, { parent_id: currentParent }),
    onSuccess: () => {
      invalidate();
      if (fileInputRef.current) fileInputRef.current.value = '';
    },
  });

  const remove = useMutation({
    mutationFn: (id: number) => deleteWorkplaceDocument(code, id),
    onSuccess: invalidate,
  });

  const share = useMutation({
    mutationFn: (doc: WorkplaceDocument) =>
      doc.is_shared ? unshareWorkplaceDocument(code, doc.id) : shareWorkplaceDocument(code, doc.id),
    onSuccess: invalidate,
  });

  const transfer = useMutation({
    mutationFn: ({ doc, activityId }: { doc: WorkplaceDocument; activityId: number }) =>
      transferWorkplaceDocument(code, doc.id, activityId),
    onSuccess: () => {
      invalidate();
      setTransferTarget(null);
    },
  });

  const navigateTo = (folder: WorkplaceDocument) => {
    if (!folder.is_folder) return;
    setPath((p) => [...p, { id: folder.id, name: folder.name }]);
  };

  const goTo = (index: number) => setPath((p) => p.slice(0, index + 1));

  return (
    <div className="flex flex-col gap-4">
      <header className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h3 className="flex items-center gap-2 text-lg font-semibold">
            <Icon name="folderOpen" className="h-5 w-5 text-muted-foreground" />
            Documents
          </h3>
          <p className="text-sm text-muted-foreground">
            Dossiers et fichiers de cet espace. Privés du module Records sauf partage.
          </p>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <button
            type="button"
            onClick={() => setShowFolderForm((v) => !v)}
            className="flex items-center gap-1.5 rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted"
          >
            <Icon name="folderPlus" className="h-4 w-4" />
            Nouveau dossier
          </button>
          <button
            type="button"
            onClick={() => fileInputRef.current?.click()}
            className="flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground hover:opacity-90"
          >
            <Icon name="upload" className="h-4 w-4" />
            Uploader un fichier
          </button>
          <input
            ref={fileInputRef}
            type="file"
            className="hidden"
            onChange={(e) => {
              const file = e.target.files?.[0];
              if (file) upload.mutate(file);
            }}
          />
        </div>
      </header>

      {/* ===================== FIL D'ARIANE ===================== */}
      <nav className="flex flex-wrap items-center gap-1 text-sm">
        {path.map((crumb, index) => (
          <span key={`${crumb.id ?? 'root'}-${index}`} className="flex items-center gap-1">
            {index > 0 && <span className="text-muted-foreground">›</span>}
            {index === path.length - 1 ? (
              <span className="font-semibold">{crumb.name}</span>
            ) : (
              <button type="button" onClick={() => goTo(index)} className="text-primary hover:underline">
                {crumb.name}
              </button>
            )}
          </span>
        ))}
      </nav>

      {/* ===================== NOUVEAU DOSSIER ===================== */}
      {showFolderForm && (
        <form
          onSubmit={(e) => {
            e.preventDefault();
            createFolder.mutate();
          }}
          className="flex items-center gap-2 rounded-xl border border-border bg-surface p-3"
        >
          <input
            autoFocus
            value={folderName}
            onChange={(e) => setFolderName(e.target.value)}
            placeholder="Nom du dossier"
            required
            className="flex-1 rounded border border-border bg-background px-2 py-1.5 text-sm"
          />
          <button type="submit" disabled={createFolder.isPending} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-60">
            Créer
          </button>
          <button type="button" onClick={() => setShowFolderForm(false)} className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted">
            Annuler
          </button>
        </form>
      )}

      {/* ===================== LISTE ===================== */}
      <section className="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
        {isLoading ? (
          <p className="px-4 py-12 text-center text-sm text-muted-foreground">Chargement…</p>
        ) : isError ? (
          <p className="px-4 py-12 text-center text-sm text-muted-foreground">Impossible de charger les documents.</p>
        ) : items.length === 0 ? (
          <div className="px-4 py-12 text-center">
            <Icon name="folderOpen" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/20" />
            <p className="text-sm font-medium text-muted-foreground">Aucun document</p>
            <p className="text-xs text-muted-foreground">Créez un dossier ou uploadez un fichier pour commencer.</p>
          </div>
        ) : (
          <ul className="divide-y divide-border">
            {items.map((doc) => (
              <li key={doc.id} className="flex items-center gap-3 px-4 py-2.5 hover:bg-muted/50">
                <span className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${doc.is_folder ? 'bg-amber-100 text-amber-600' : 'bg-sky-100 text-sky-600'}`}>
                  <Icon name={doc.is_folder ? 'folderOpen' : 'fileText'} className="h-4 w-4" />
                </span>
                <div className="min-w-0 flex-1">
                  {doc.is_folder ? (
                    <button type="button" onClick={() => navigateTo(doc)} className="truncate text-sm font-medium hover:underline">
                      {doc.name}
                    </button>
                  ) : (
                    <p className="truncate text-sm font-medium">{doc.name}</p>
                  )}
                  <p className="flex items-center gap-2 text-xs text-muted-foreground">
                    {doc.is_folder ? `${doc.children_count} élément(s)` : doc.attachment?.name ?? '—'}
                    {doc.is_shared && (
                      <span className="inline-flex items-center gap-0.5 rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700">
                        <Icon name="globe" className="h-3 w-3" />
                        Partagé
                      </span>
                    )}
                  </p>
                </div>
                <span className="hidden shrink-0 text-xs text-muted-foreground sm:block">
                  {formatDate(doc.created_at)}
                </span>
                <div className="flex shrink-0 items-center gap-1.5">
                  {!doc.is_folder && (
                    <a
                      href={downloadWorkplaceDocumentUrl(code, doc.id)}
                      title="Télécharger"
                      className="rounded border border-border px-2 py-1 text-xs hover:bg-muted"
                    >
                      <Icon name="download" className="h-3.5 w-3.5" />
                    </a>
                  )}
                  <button
                    type="button"
                    title={doc.is_shared ? 'Ne plus partager (module Records)' : 'Partager vers le module Records'}
                    onClick={() => share.mutate(doc)}
                    className={`rounded border px-2 py-1 text-xs hover:bg-muted ${
                      doc.is_shared ? 'border-emerald-400 text-emerald-700' : 'border-border text-muted-foreground'
                    }`}
                  >
                    <Icon name="globe" className="h-3.5 w-3.5" />
                  </button>
                  <button
                    type="button"
                    title="Transférer vers le module Records"
                    onClick={() => setTransferTarget(doc)}
                    className="rounded border border-primary/40 px-2 py-1 text-xs text-primary hover:bg-primary/10"
                  >
                    <Icon name="arrowRightSquare" className="h-3.5 w-3.5" />
                  </button>
                  <button
                    type="button"
                    title="Supprimer"
                    onClick={() => {
                      if (window.confirm(`Supprimer « ${doc.name} » ?`)) remove.mutate(doc.id);
                    }}
                    className="rounded border border-danger/40 px-2 py-1 text-xs text-danger hover:bg-danger/10"
                  >
                    <Icon name="trash" className="h-3.5 w-3.5" />
                  </button>
                </div>
              </li>
            ))}
          </ul>
        )}
      </section>

      {(upload.isError || createFolder.isError || share.isError || transfer.isError) && (
        <p className="rounded border border-danger/40 bg-danger/5 px-3 py-2 text-sm text-danger">
          L'opération a échoué. Vérifiez vos droits et les données saisies.
        </p>
      )}

      {/* ===================== MODALE DE TRANSFERT ===================== */}
      {transferTarget && (
        <TransferModal
          document={transferTarget}
          pending={transfer.isPending}
          onConfirm={(activityId) => transfer.mutate({ doc: transferTarget, activityId })}
          onClose={() => setTransferTarget(null)}
        />
      )}
    </div>
  );
}

function TransferModal({
  document,
  pending,
  onConfirm,
  onClose,
}: {
  document: WorkplaceDocument;
  pending: boolean;
  onConfirm: (activityId: number) => void;
  onClose: () => void;
}) {
  const { data, isLoading } = useQuery({
    queryKey: ['activities', 'transfer'],
    queryFn: listClassificationActivities,
  });
  const activities = data?.data ?? [];
  const [selected, setSelected] = useState<string>('');

  const options = buildActivityOptions(activities);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" onClick={onClose}>
      <div
        className="w-full max-w-lg rounded-xl border border-border bg-background p-5 shadow-lg"
        onClick={(e) => e.stopPropagation()}
      >
        <header className="flex items-start justify-between gap-3">
          <div>
            <h4 className="flex items-center gap-2 text-base font-semibold">
              <Icon name="arrowRightSquare" className="h-5 w-5 text-primary" />
              Transférer vers le module Records
            </h4>
            <p className="mt-1 text-sm text-muted-foreground">
              « {document.name} » quittera cet espace et ne sera accessible que via le module Records.
            </p>
          </div>
          <button type="button" onClick={onClose} className="rounded p-1 text-muted-foreground hover:bg-muted" aria-label="Fermer">
            <Icon name="close" className="h-4 w-4" />
          </button>
        </header>

        <form
          onSubmit={(e) => {
            e.preventDefault();
            if (selected) onConfirm(Number(selected));
          }}
          className="mt-4 flex flex-col gap-3"
        >
          <label className="flex flex-col gap-1 text-sm">
            <span>Classe du plan de classement <span className="text-danger">*</span></span>
            <select
              value={selected}
              onChange={(e) => setSelected(e.target.value)}
              required
              className="max-h-64 rounded border border-border bg-background px-2 py-1.5 text-sm"
              size={Math.min(options.length || 1, 8)}
            >
              {isLoading ? (
                <option value="">Chargement…</option>
              ) : options.length === 0 ? (
                <option value="">Aucune classe disponible</option>
              ) : (
                options.map((o) => (
                  <option key={o.activity.id} value={o.activity.id}>
                    {'\u00A0\u00A0'.repeat(o.depth) + (o.activity.code ? `${o.activity.code} — ` : '') + o.activity.name}
                  </option>
                ))
              )}
            </select>
          </label>
          <p className="text-xs text-muted-foreground">
            Le transfert affecte le document à la classe choisie : il devient une notice du module Records.
          </p>
          <footer className="flex justify-end gap-2">
            <button type="button" onClick={onClose} className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted">
              Annuler
            </button>
            <button
              type="submit"
              disabled={pending || !selected || options.length === 0}
              className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-60"
            >
              {pending ? 'Transfert…' : 'Transférer'}
            </button>
          </footer>
        </form>
      </div>
    </div>
  );
}

/** Construit une liste aplatie des classes avec profondeur (plan de classement). */
function buildActivityOptions(activities: ActivityItem[]): { activity: ActivityItem; depth: number }[] {
  const byId = new Map(activities.map((a) => [a.id, a]));
  const depthOf = new Map<number, number>();

  const computeDepth = (a: ActivityItem, seen: Set<number>): number => {
    if (depthOf.has(a.id)) return depthOf.get(a.id)!;
    if (seen.has(a.id)) return 0;
    seen.add(a.id);
    let depth = 0;
    if (a.parent_id != null) {
      const parent = byId.get(a.parent_id);
      if (parent) depth = computeDepth(parent, seen) + 1;
    }
    depthOf.set(a.id, depth);
    return depth;
  };

  return [...activities]
    .sort((a, b) => a.name.localeCompare(b.name))
    .map((a) => ({ activity: a, depth: computeDepth(a, new Set()) }));
}
