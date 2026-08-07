'use client';

import { useEffect } from 'react';
import { useQuery } from '@tanstack/react-query';
import { createResourceApi } from '@/lib/api/resources';
import * as recordApi from '../services/record.service';

/** Référentiels sans écran d'administration dédié — juste des options de sélecteur. */
export const recordLevelsApi = createResourceApi('record-levels');
export const recordConfidentialitiesApi = createResourceApi('record-confidentialities');

type MetadataField = {
  code: string;
  name: string;
  data_type: string;
  options?: string[] | null;
  value?: unknown;
  required: boolean;
  readonly: boolean;
  group?: string | null;
};

function groupFields(fields: MetadataField[]): [string, MetadataField[]][] {
  const groups: [string, MetadataField[]][] = [];
  for (const field of fields) {
    const label = field.group ?? 'Métadonnées';
    const last = groups[groups.length - 1];
    if (last && last[0] === label) last[1].push(field);
    else groups.push([label, [field]]);
  }
  return groups;
}

function MetadataInput({ field, value, onChange }: { field: MetadataField; value: string; onChange: (v: string) => void }) {
  const common = 'rounded border border-border bg-background px-2 py-1.5 text-sm';

  if (field.data_type === 'boolean') {
    return <input type="checkbox" checked={value === '1' || value === 'true'} disabled={field.readonly} onChange={(e) => onChange(e.target.checked ? '1' : '0')} className="h-4 w-4" />;
  }
  if (field.data_type === 'date') {
    return <input type="date" value={value} disabled={field.readonly} onChange={(e) => onChange(e.target.value)} className={common} />;
  }
  if (field.data_type === 'number') {
    return <input type="number" value={value} disabled={field.readonly} onChange={(e) => onChange(e.target.value)} className={common} />;
  }
  if (field.options && field.options.length > 0) {
    return (
      <select value={value} disabled={field.readonly} onChange={(e) => onChange(e.target.value)} className={common}>
        <option value="">—</option>
        {field.options.map((o) => <option key={o} value={o}>{o}</option>)}
      </select>
    );
  }
  if (field.data_type === 'textarea' || field.data_type === 'text_long') {
    return <textarea value={value} disabled={field.readonly} onChange={(e) => onChange(e.target.value)} rows={3} className={common} />;
  }
  return <input type="text" value={value} disabled={field.readonly} onChange={(e) => onChange(e.target.value)} className={common} />;
}

/**
 * Champs dynamiques du type sélectionné (voir `Record::getVisibleMetadataFields()`
 * / `RecordType::getVisibleMetadataDefinitions()` côté Laravel) — les anciens
 * champs descriptifs figés (biographical_history, content, ...) sont désormais
 * des `MetadataDefinition` par type, jusqu'ici invisibles dans le formulaire
 * Next.js (voir gap A.02).
 */
export function MetadataFieldsSection({ recordId, typeId, value, onChange }: {
  recordId?: string;
  typeId?: string;
  value: Record<string, string>;
  onChange: (v: Record<string, string>) => void;
}) {
  const { data } = useQuery({
    queryKey: ['metadata-fields', recordId ?? 'new', typeId],
    queryFn: () => (recordId ? recordApi.getRecordMetadataFields(recordId) : recordApi.getRecordTypeMetadataFields(typeId as string)),
    enabled: Boolean(recordId || typeId),
  });

  const fields = ((data?.data ?? []) as unknown as MetadataField[]);

  // Pré-remplit depuis les valeurs serveur (édition) une fois les champs chargés,
  // sans écraser ce que l'agent a déjà saisi dans cette session.
  useEffect(() => {
    if (fields.length === 0) return;
    const next = { ...value };
    let changed = false;
    for (const field of fields) {
      if (!(field.code in next) && field.value !== undefined && field.value !== null) {
        next[field.code] = String(field.value);
        changed = true;
      }
    }
    if (changed) onChange(next);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [fields.length]);

  if (!recordId && !typeId) {
    return (
      <div className="rounded border border-border bg-surface p-4 text-sm text-muted-foreground">
        Sélectionnez une typologie pour afficher ses métadonnées.
      </div>
    );
  }

  if (fields.length === 0) {
    return (
      <div className="rounded border border-border bg-surface p-4 text-sm text-muted-foreground">
        Cette typologie ne définit aucune métadonnée visible.
      </div>
    );
  }

  return (
    <fieldset className="flex flex-col gap-4 rounded border border-border bg-surface p-4">
      <legend className="px-1 text-sm font-semibold">Métadonnées</legend>
      {groupFields(fields).map(([group, groupFieldsList]) => (
        <div key={group} className="flex flex-col gap-3">
          <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{group}</p>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            {groupFieldsList.map((field) => (
              <label key={field.code} className="flex flex-col gap-1 text-sm">
                <span>{field.name} {field.required && <span className="text-danger">*</span>}</span>
                <MetadataInput
                  field={field}
                  value={value[field.code] ?? ''}
                  onChange={(v) => onChange({ ...value, [field.code]: v })}
                />
              </label>
            ))}
          </div>
        </div>
      ))}
    </fieldset>
  );
}
