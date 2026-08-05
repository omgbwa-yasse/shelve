'use client';

import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api/client';
import type { Tab } from '@/lib/crud/types';
import type { Entity } from '@/lib/api/types';

/**
 * Liste d'une sous-ressource affichée en onglet sur une fiche détail
 * (ex. `records/{id}/children`, `slips/{id}/records`, `workplaces/{id}/members`).
 * Rendu générique depuis la config d'onglet (colonnes, suppression, ajout).
 */
export function RelatedList({ tab, parentId }: { tab: Tab; parentId: string }) {
  const queryClient = useQueryClient();
  const [page, setPage] = useState(1);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState<Record<string, string>>({});

  const parentBase = tab.parentApi?.base ?? '';
  const subPath = tab.apiPath ?? '';
  // Chemin complet : `queryBy` (ressource plate) sinon `{base}/{id}/{subPath}`.
  const url = tab.queryBy ? tab.queryBy(parentId) : `${parentBase}/${parentId}${subPath ? `/${subPath}` : ''}`;
  const queryKey = [url, page];

  const { data, isPending } = useQuery({
    queryKey,
    queryFn: async () =>
      (await apiFetch<{ data: Entity[] }>(`${url}?page=${page}&per_page=20`)) as unknown as {
        data: Entity[];
      },
  });

  const rows = data?.data ?? [];

  const removeMutation = useMutation({
    mutationFn: (id: string | number) => apiFetch<unknown>(`${url}/${id}`, { method: 'DELETE' }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey }),
  });

  async function addRow(e: React.FormEvent) {
    e.preventDefault();
    if (!tab.addVerb) return;
    await apiFetch<unknown>(`${parentBase}/${parentId}/${tab.addVerb}`, { method: 'POST', body: form });
    setForm({});
    setShowForm(false);
    queryClient.invalidateQueries({ queryKey });
  }

  return (
    <div className="flex flex-col gap-3">
      <div className="min-h-0 overflow-auto rounded border border-border">
        <table className="w-full text-left text-sm">
          <thead className="sticky top-0 bg-surface">
            <tr>
              {tab.columns.map((c) => (
                <th key={c.key} className="px-3 py-2 font-medium text-muted-foreground">
                  {c.label}
                </th>
              ))}
              {tab.deletable && <th className="px-3 py-2" />}
            </tr>
          </thead>
          <tbody>
            {isPending && (
              <tr><td colSpan={tab.columns.length + 1} className="px-3 py-6 text-center text-muted-foreground">Chargement…</td></tr>
            )}
            {rows.map((row) => (
              <tr key={String(row.id)} className="border-t border-border/60 hover:bg-muted">
                {tab.columns.map((c) => (
                  <td key={c.key} className="px-3 py-2">
                    {c.render ? c.render(row[c.key], row) : renderValue(row[c.key])}
                  </td>
                ))}
                {tab.deletable && (
                  <td className="px-3 py-2 text-right">
                    <button
                      type="button"
                      onClick={() => { if (window.confirm('Supprimer cette ligne ?')) removeMutation.mutate(row.id!); }}
                      className="rounded border border-border px-2 py-1 text-xs text-danger hover:bg-muted"
                    >
                      Supprimer
                    </button>
                  </td>
                )}
              </tr>
            ))}
            {!isPending && rows.length === 0 && (
              <tr><td colSpan={tab.columns.length + 1} className="px-3 py-6 text-center text-muted-foreground">Aucun élément.</td></tr>
            )}
          </tbody>
        </table>
      </div>

      {tab.addVerb && (
        <div>
          {showForm ? (
            <form onSubmit={addRow} className="flex flex-wrap items-end gap-2 rounded border border-border bg-surface p-3">
              {(tab.fields ?? []).map((f) => (
                <label key={f.name} className="flex flex-col gap-1 text-xs font-medium">
                  {f.label}
                  <input
                    type="text"
                    value={form[f.name] ?? ''}
                    onChange={(e) => setForm((p) => ({ ...p, [f.name]: e.target.value }))}
                    className="rounded border border-border bg-background px-2 py-1 text-sm"
                  />
                </label>
              ))}
              <div className="flex gap-1">
                <button type="submit" className="rounded bg-primary px-3 py-1.5 text-xs text-primary-foreground">Ajouter</button>
                <button type="button" onClick={() => setShowForm(false)} className="rounded border border-border px-3 py-1.5 text-xs">Annuler</button>
              </div>
            </form>
          ) : (
            <button type="button" onClick={() => setShowForm(true)} className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted">
              + Ajouter
            </button>
          )}
        </div>
      )}
    </div>
  );
}

function renderValue(value: unknown): React.ReactNode {
  if (value === null || value === undefined || value === '') return <span className="text-muted-foreground/60">—</span>;
  if (typeof value === 'boolean') return value ? 'Oui' : 'Non';
  if (typeof value === 'object') return JSON.stringify(value);
  return String(value);
}
