'use client';

import { useQuery } from '@tanstack/react-query';
import { PageHeader } from '@/components/ui/page';
import { InfoScreen } from '@/components/ui/page';
import { recordsApi } from '../services/record.service';

/** Arbre hiérarchique des notices (feature Records). */
export function RecordsTree() {
  const { data } = useQuery({
    queryKey: ['records-tree'],
    queryFn: async () => (await recordsApi.list({ per_page: 200 })) as { data: any[] },
  });
  const records = data?.data ?? [];

  function renderTree(parentId: number | null, depth: number): React.ReactNode {
    return records
      .filter((r) => (r.parent_id ?? null) === parentId)
      .map((r) => (
        <div key={r.id} className="py-1" style={{ paddingLeft: depth * 20 }}>
          <span className="font-mono text-xs text-muted-foreground">{r.code ?? ''}</span> {r.name ?? ''}
          {renderTree(r.id, depth + 1)}
        </div>
      ));
  }

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Arbre des notices" description="Hiérarchie des notices d'archives (parent/enfant)." />
      <div className="min-h-0 flex-1 overflow-auto rounded border border-border bg-surface p-4 text-sm">
        {renderTree(null, 0)}
        {records.length === 0 && <p className="text-muted-foreground">Aucune notice.</p>}
      </div>
    </div>
  );
}

/** Téléversement par glisser-déposer (pas d'endpoint API v1). */
export function DragDrop() {
  return (
    <InfoScreen
      title="Drag & Drop"
      description="Téléversement de documents par glisser-déposer."
      sections={[
        ['Non porté', "Le drag & drop de fichiers (RecordDragDropController) n'a pas d'endpoint API v1. Utiliser le formulaire de création de notice avec pièce jointe."],
        ['Alternative', 'Créer une notice → onglet Pièces jointes → téléversement.'],
      ]}
    />
  );
}
