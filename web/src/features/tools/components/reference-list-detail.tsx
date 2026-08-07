'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  getReferenceList,
  addReferenceValue,
  updateReferenceValue,
  deleteReferenceValue,
} from '@/features/tools/services/tool.service';
import { Icon } from '@/components/icons';
import { formatDate } from '@/utils/format-date';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { DataTable } from '@/components/ui/table';

type ValueRow = Entity & {
  id: number;
  code: string;
  value: string;
  description?: string | null;
  active: boolean;
  created_at?: string;
};

/**
 * Fiche d'un domaine de valeurs — reproduit `settings/reference-lists/show.blade.php` :
 * gestion des valeurs (actives / inactives), ajout, bascule actif/inactif, suppression.
 */
export function ReferenceListDetail({ id }: { id: string }) {
  const queryClient = useQueryClient();
  const [tab, setTab] = useState<'active' | 'inactive'>('active');
  const [form, setForm] = useState<{ code: string; value: string; description: string }>({
    code: '',
    value: '',
    description: '',
  });

  const { data, isLoading, isError } = useQuery({
    queryKey: ['reference-list', id],
    queryFn: () => getReferenceList(id),
  });
  const list = data?.data ?? {};
  const values = ((list.values as Entity[] | undefined) ?? []) as ValueRow[];

  const invalidate = () => queryClient.invalidateQueries({ queryKey: ['reference-list', id] });

  const addValue = useMutation({
    mutationFn: () => addReferenceValue(id, { ...form, active: true }),
    onSuccess: () => {
      invalidate();
      setForm({ code: '', value: '', description: '' });
    },
  });

  const toggle = useMutation({
    mutationFn: (value: ValueRow) => updateReferenceValue(id, value.id, { active: !value.active }),
    onSuccess: invalidate,
  });

  const remove = useMutation({
    mutationFn: (value: ValueRow) => deleteReferenceValue(id, value.id),
    onSuccess: invalidate,
  });

  const visibleValues = values.filter((v) => tab === 'active' ? v.active : !v.active);

  const COLS: TableColumn<ValueRow>[] = [
    { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
    { key: 'value', label: 'Valeur' },
    { key: 'description', label: 'Description', render: (r) => (r.description ? String(r.description) : '—') },
    { key: 'created_at', label: 'Créée le', render: (r) => (r.created_at ? formatDate(r.created_at) : '—') },
  ];

  return (
    <div className="flex flex-col gap-4">
      <header className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold">{String(list.name ?? '')}</h1>
          <p className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
            <code className="rounded bg-muted px-1.5 py-0.5 font-mono text-xs">{String(list.code ?? '')}</code>
            {list.description ? String(list.description) : ''}
            <span className="rounded-full bg-muted px-2 py-0.5 text-xs">{values.length} valeur(s)</span>
          </p>
        </div>
        <div className="flex gap-2">
          <Link href="/tools/reference-lists" className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted">
            Retour
          </Link>
        </div>
      </header>

      {/* ===================== ONGLETS ===================== */}
      <nav className="flex gap-1 border-b border-border text-sm">
        <button
          type="button"
          onClick={() => setTab('active')}
          className={`rounded-t border-b-2 px-3 py-1.5 ${tab === 'active' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:bg-muted'}`}
        >
          Actives ({values.filter((v) => v.active).length})
        </button>
        <button
          type="button"
          onClick={() => setTab('inactive')}
          className={`rounded-t border-b-2 px-3 py-1.5 ${tab === 'inactive' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:bg-muted'}`}
        >
          Inactives ({values.filter((v) => !v.active).length})
        </button>
      </nav>

      {isLoading ? (
        <p className="py-10 text-center text-sm text-muted-foreground">Chargement…</p>
      ) : isError ? (
        <p className="py-10 text-center text-sm text-muted-foreground">Impossible de charger le domaine.</p>
      ) : (
        <div className="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
          <DataTable
            columns={COLS}
            rows={visibleValues}
            loading={false}
            emptyLabel={tab === 'active' ? 'Aucune valeur active dans ce domaine.' : 'Aucune valeur inactive.'}
            actions={(row) => (
              <div className="flex gap-1.5">
                <button
                  type="button"
                  title={row.active ? 'Désactiver' : 'Activer'}
                  onClick={() => toggle.mutate(row as ValueRow)}
                  className="rounded border border-border px-2 py-1 text-xs hover:bg-muted"
                >
                  {row.active ? 'Désactiver' : 'Activer'}
                </button>
                <button
                  type="button"
                  title="Supprimer"
                  onClick={() => {
                    if (window.confirm(`Supprimer la valeur « ${String(row.value ?? '')} » ?`)) remove.mutate(row as ValueRow);
                  }}
                  className="rounded border border-danger/40 px-2 py-1 text-xs text-danger hover:bg-danger/10"
                >
                  Supprimer
                </button>
              </div>
            )}
          />
        </div>
      )}

      {/* ===================== AJOUT DE VALEUR ===================== */}
      {tab === 'active' && (
        <form
          onSubmit={(e) => {
            e.preventDefault();
            addValue.mutate();
          }}
          className="grid grid-cols-1 gap-3 rounded-xl border border-border bg-surface p-4 shadow-sm md:grid-cols-4"
        >
          <label className="flex flex-col gap-1 text-sm">
            <span>Code <span className="text-danger">*</span></span>
            <input value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value })} required className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span>Valeur <span className="text-danger">*</span></span>
            <input value={form.value} onChange={(e) => setForm({ ...form, value: e.target.value })} required className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span>Description</span>
            <input value={form.description} onChange={(e) => setForm({ ...form, description: e.target.value })} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
          </label>
          <div className="flex items-end">
            <button type="submit" disabled={addValue.isPending} className="w-full rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-60">
              + Ajouter
            </button>
          </div>
        </form>
      )}

      {(addValue.isError || toggle.isError || remove.isError) && (
        <p className="rounded border border-danger/40 bg-danger/5 px-3 py-2 text-sm text-danger">
          L'opération a échoué (code peut-être déjà utilisé).
        </p>
      )}
    </div>
  );
}
