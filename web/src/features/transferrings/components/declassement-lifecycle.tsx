'use client';

import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { PageHeader } from '@/components/ui/page';
import { Icon } from '@/components/icons';
import * as api from '../services/transferring.service';

const STAGES: { key: api.LifecycleStage; label: string; hint: string }[] = [
  { key: 'avant_dua', label: 'Avant DUA', hint: 'Durée d\'utilité administrative (communicabilité) non écoulée' },
  { key: 'apres_dua', label: 'Après DUA', hint: 'Communicabilité écoulée — avant la durée légale (rétention)' },
  { key: 'apres_dul', label: 'Après DUL', hint: 'Durée légale (rétention + loi) écoulée — sort final applicable' },
];

const STAGE_ACCENT: Record<api.LifecycleStage, string> = {
  avant_dua: 'text-primary',
  apres_dua: 'text-yellow-600',
  apres_dul: 'text-danger',
};

/**
 * Cycle de vie des notices selon la logique DUA (communicabilité) / DUL
 * (rétention rattachée à une loi) et le sort final (E/T/C).
 */
export function DeclassementLifecycle() {
  const [stage, setStage] = useState<api.LifecycleStage>('avant_dua');

  const { data, isLoading, isError } = useQuery({
    queryKey: ['declassement-lifecycle', stage],
    queryFn: () => api.getDeclassementLifecycle(stage),
  });
  const records = data?.data ?? [];

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader
        title="Cycle de vie des documents"
        description="Documents classés selon la durée d'utilité administrative (communicabilité), la durée d'utilité légale (rétention rattachée à une loi) et le sort final."
      />

      <div className="flex flex-col gap-2">
        <div className="flex flex-wrap gap-1 rounded-xl border border-border bg-surface p-1">
          {STAGES.map((s) => {
            const active = stage === s.key;
            return (
              <button
                key={s.key}
                type="button"
                onClick={() => setStage(s.key)}
                className={`flex flex-1 flex-col items-center gap-0.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${active ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted'}`}
              >
                <span className="flex items-center gap-1.5">
                  <Icon name="checkCircle" className={`h-4 w-4 ${active ? '' : 'opacity-0'}`} />
                  {s.label}
                </span>
                <span className="text-[10px] font-normal text-muted-foreground">{s.hint}</span>
              </button>
            );
          })}
        </div>
        {isError && <p className="text-sm text-danger">Impossible de charger le cycle de vie.</p>}
      </div>

      {isLoading ? (
        <p className="text-sm text-muted-foreground">Chargement…</p>
      ) : records.length === 0 ? (
        <div className="flex flex-col items-center gap-2 rounded border border-border bg-surface p-8 text-center text-sm text-muted-foreground">
          <Icon name="folderOpen" className="h-10 w-10 text-muted-foreground/40" />
          Aucun document dans ce stade.
        </div>
      ) : (
        <div className="min-w-0 overflow-auto rounded border border-border bg-surface">
          <table className="w-full text-left text-sm">
            <thead className="sticky top-0 bg-surface">
              <tr>
                <th className="px-3 py-2 font-medium text-muted-foreground">Code</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">Nom</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">Date réf.</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">DUA fin</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">DUL fin</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">Sort</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">Règle légale (loi)</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">Stade</th>
              </tr>
            </thead>
            <tbody>
              {records.map((r) => (
                <tr key={r.id} className="border-t border-border/60 hover:bg-muted/40">
                  <td className="px-3 py-2 font-mono text-xs">{r.code ?? '—'}</td>
                  <td className="px-3 py-2">{r.name ?? '—'}</td>
                  <td className="px-3 py-2 text-xs">{r.reference_date ?? '—'}</td>
                  <td className="px-3 py-2 text-xs">{r.dua_end_date ?? '—'}</td>
                  <td className="px-3 py-2 text-xs">{r.dul_end_date ?? '—'}</td>
                  <td className="px-3 py-2 text-xs font-semibold">{r.sort ?? '—'}</td>
                  <td className="px-3 py-2 text-xs">
                    {r.law ? (
                      <span className="flex flex-col">
                        <span>{r.law.law_name ?? r.law.law_code}</span>
                        {r.law.law_article_name && <span className="text-muted-foreground">Art. {r.law.law_article_name}</span>}
                      </span>
                    ) : r.retention ? (
                      <span className="text-muted-foreground">{r.retention.name} (pas de loi)</span>
                    ) : (
                      '—'
                    )}
                  </td>
                  <td className={`px-3 py-2 text-xs font-semibold ${STAGE_ACCENT[(r.stage ?? 'avant_dua') as api.LifecycleStage]}`}>
                    {STAGES.find((s) => s.key === r.stage)?.label ?? r.stage}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
