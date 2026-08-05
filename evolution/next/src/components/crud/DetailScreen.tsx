'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import type { Field, ResourceConfig } from '@/lib/crud/types';
import type { Entity } from '@/lib/api/types';
import { RelatedList } from '@/components/crud/RelatedList';

/**
 * Écran DÉTAIL universel — rendu depuis une `ResourceConfig` : fiche des
 * champs, onglets de sous-ressources, actions métier, édition/suppression.
 */
export function DetailScreen({ config, id }: { config: ResourceConfig; id: string }) {
  const router = useRouter();
  const queryClient = useQueryClient();

  const { data, isPending, isError } = useQuery({
    queryKey: [config.path, id],
    queryFn: async () => (await config.api.show(id)) as { data: Entity },
  });

  const removeMutation = useMutation({
    mutationFn: () => config.api.destroy(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: [config.path] });
      router.push(config.path);
      router.refresh();
    },
  });

  const [activeTab, setActiveTab] = useState<string | null>(null);

  if (isPending) return <p className="text-sm text-muted-foreground">Chargement…</p>;
  if (isError || !data?.data) return <p className="text-sm text-danger">Impossible de charger la notice.</p>;

  const entity = data.data;
  const title = config.titleKey ? String(entity[config.titleKey] ?? '') : String(entity.id ?? '');
  const code = config.codeKey ? String(entity[config.codeKey] ?? '') : '';
  const visibleFields = config.fields.filter((f) => !f.hidden && (config.detailFields ? config.detailFields.includes(f.name) : true));

  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex flex-wrap items-start justify-between gap-3">
        <div>
          {code && <p className="text-xs font-mono text-muted-foreground">{code}</p>}
          <h1 className="text-xl font-semibold">{title}</h1>
        </div>
        <div className="flex flex-wrap gap-2">
          {config.rowActions?.map((action) => (
            <button
              key={action.label}
              type="button"
              onClick={() => {
                if (action.confirm && !window.confirm(action.confirm)) return;
                if (action.method === 'destroy') removeMutation.mutate();
              }}
              className={`rounded border border-border px-3 py-1.5 text-sm hover:bg-muted ${action.variant === 'danger' ? 'text-danger' : ''}`}
            >
              {action.label}
            </button>
          ))}
          {config.editable !== false && (
            <Link href={`${config.path}/${id}/edit`} className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted">
              Modifier
            </Link>
          )}
          {config.deletable !== false && (
            <button
              type="button"
              onClick={() => { if (window.confirm(`Supprimer cette entrée ?`)) removeMutation.mutate(); }}
              className="rounded border border-danger/40 bg-danger/10 px-3 py-1.5 text-sm text-danger hover:bg-danger/20"
            >
              Supprimer
            </button>
          )}
        </div>
      </header>

      {config.tabs && config.tabs.length > 0 && (
        <div>
          <nav className="flex gap-1 border-b border-border text-sm">
            {config.tabs.map((tab, i) => (
              <button
                key={tab.key}
                type="button"
                onClick={() => setActiveTab(activeTab === tab.key ? null : tab.key)}
                className={`rounded-t border-b-2 px-3 py-1.5 ${
                  activeTab === tab.key ? 'border-primary' : 'border-transparent text-muted-foreground hover:bg-muted'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </nav>
          {activeTab && (
            <div className="mt-3">
              {(() => {
                const tab = config.tabs!.find((t) => t.key === activeTab);
                return tab ? (
                  <RelatedList tab={tab} parentId={id} />
                ) : null;
              })()}
            </div>
          )}
        </div>
      )}

      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        {visibleFields.map((field) => (
          <DetailField key={field.name} field={field} value={entity[field.name]} />
        ))}
      </div>
    </div>
  );
}

function DetailField({ field, value }: { field: Field; value: unknown }) {
  return (
    <div className="rounded border border-border bg-surface p-3">
      <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{field.label}</p>
      <p className="mt-1 text-sm">{renderValue(field, value)}</p>
    </div>
  );
}

function renderValue(field: Field, value: unknown): React.ReactNode {
  if (value === null || value === undefined || value === '') return <span className="text-muted-foreground/60">—</span>;
  if (field.type === 'boolean') return value ? 'Oui' : 'Non';
  if (field.type === 'select' || field.type === 'reference') {
    if (field.displayKey && typeof value === 'object') return String((value as Record<string, unknown>)[field.displayKey] ?? '—');
    const opt = (field.options ?? []).find((o) => String(o.value) === String(value));
    return opt ? opt.label : String(value);
  }
  if (field.type === 'date' || field.type === 'datetime') {
    const d = new Date(String(value));
    return isNaN(d.getTime()) ? String(value) : d.toLocaleDateString('fr-FR');
  }
  if (typeof value === 'object') return JSON.stringify(value);
  return String(value);
}
