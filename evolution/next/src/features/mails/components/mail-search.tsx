'use client';

import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { PageHeader } from '@/components/ui/page';
import { mailsApi } from '../services/mail.service';
import type { Entity } from '@/lib/api/types';

const STATUS_OPTIONS = [
  ['draft', 'Brouillon'], ['pending_review', 'En attente de révision'], ['in_progress', 'En cours de traitement'],
  ['pending_approval', "En attente d'approbation"], ['approved', 'Approuvé'], ['transmitted', 'Transmis'],
  ['completed', 'Terminé'], ['rejected', 'Rejeté'], ['cancelled', 'Annulé'], ['overdue', 'En retard'],
] as const;

/**
 * Recherche avancée des courriers (feature Mails) — critères combinables,
 * résultat paginé via l'API (convention `filter[...]`).
 */
export function MailAdvancedSearch() {
  const [criteria, setCriteria] = useState<Record<string, string>>({});
  const [applied, setApplied] = useState<Record<string, string>>({});

  const params: Record<string, unknown> = { page: 1, 'page.size': 25 };
  for (const [k, v] of Object.entries(applied)) {
    if (k === 'date') {
      params['filter[date][between]'] = v;
    } else if (k === 'date_from') {
      params['filter[date][gte]'] = v;
    } else if (k === 'date_to') {
      params['filter[date][lte]'] = v;
    } else if (v) {
      params[`filter[${k}]`] = v;
    }
  }

  const { data, isPending } = useQuery({
    queryKey: ['mails-advanced', applied],
    enabled: Object.keys(applied).length > 0,
    queryFn: async () => (await mailsApi.list(params as never)) as { data: Entity[]; meta?: { total: number } },
  });

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Recherche avancée — Courriers" description="Filtres combinables sur le courrier (code, objet, statut, type, période)." />

      <div className="grid w-full grid-cols-1 gap-3 rounded border border-border bg-surface p-4 md:grid-cols-3">
        <Field label="Code" value={criteria.code ?? ''} onChange={(v) => setCriteria((p) => ({ ...p, code: v }))} />
        <Field label="Objet / Nom" value={criteria.name ?? ''} onChange={(v) => setCriteria((p) => ({ ...p, name: v }))} />
        <label className="flex flex-col gap-1 text-sm">
          Statut
          <select value={criteria.status ?? ''} onChange={(e) => setCriteria((p) => ({ ...p, status: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5 text-sm">
            <option value="">—</option>
            {STATUS_OPTIONS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
          </select>
        </label>
        <label className="flex flex-col gap-1 text-sm">
          Type
          <select value={criteria.mail_type ?? ''} onChange={(e) => setCriteria((p) => ({ ...p, mail_type: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5 text-sm">
            <option value="">—</option>
            <option value="internal">Interne</option>
            <option value="incoming">Entrant</option>
            <option value="outgoing">Sortant</option>
          </select>
        </label>
        <Field label="Date début" type="date" value={criteria.dateFrom ?? ''} onChange={(v) => setCriteria((p) => ({ ...p, dateFrom: v }))} />
        <Field label="Date fin" type="date" value={criteria.dateTo ?? ''} onChange={(v) => setCriteria((p) => ({ ...p, dateTo: v }))} />
        <div className="flex items-end gap-2 md:col-span-3">
          <button
            type="button"
            onClick={() => {
              const next: Record<string, string> = {};
              if (criteria.code) next.code = criteria.code;
              if (criteria.name) next.name = criteria.name;
              if (criteria.status) next.status = criteria.status;
              if (criteria.mail_type) next.mail_type = criteria.mail_type;
              if (criteria.dateFrom && criteria.dateTo) {
                next.date = `${criteria.dateFrom},${criteria.dateTo}`;
              } else {
                if (criteria.dateFrom) next.date_from = criteria.dateFrom;
                if (criteria.dateTo) next.date_to = criteria.dateTo;
              }
              setApplied(next);
            }}
            className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90"
          >
            Rechercher
          </button>
          <button type="button" onClick={() => { setCriteria({}); setApplied({}); }} className="rounded border border-border px-4 py-2 text-sm hover:bg-muted">
            Réinitialiser
          </button>
        </div>
      </div>

      {Object.keys(applied).length > 0 && (
        <div className="min-h-0 flex-1 overflow-auto rounded border border-border">
          <table className="w-full text-left text-sm">
            <thead className="sticky top-0 bg-surface">
              <tr>
                <th className="px-3 py-2 font-medium text-muted-foreground">Code</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">Nom</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">Date</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">Statut</th>
              </tr>
            </thead>
            <tbody>
              {isPending && <tr><td colSpan={4} className="px-3 py-6 text-center text-muted-foreground">Recherche…</td></tr>}
              {(data?.data ?? []).map((m) => (
                <tr key={String(m.id)} className="border-t border-border/60 hover:bg-muted">
                  <td className="px-3 py-2 font-mono text-xs">{String(m.code ?? '')}</td>
                  <td className="px-3 py-2">{String(m.name ?? '')}</td>
                  <td className="px-3 py-2">{m.date ? new Date(String(m.date)).toLocaleDateString('fr-FR') : '—'}</td>
                  <td className="px-3 py-2">{String(m.status ?? '')}</td>
                </tr>
              ))}
              {!isPending && (data?.data ?? []).length === 0 && (
                <tr><td colSpan={4} className="px-3 py-6 text-center text-muted-foreground">Aucun résultat.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

/** Sélection par date : courriers d'une période. */
export function MailDateSelect() {
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');
  const [applied, setApplied] = useState<{ from: string; to: string } | null>(null);

  const params: Record<string, unknown> = { page: 1, 'page.size': 50 };
  if (applied) {
    params['filter[date][gte]'] = applied.from || undefined;
    params['filter[date][lte]'] = applied.to || undefined;
  }

  const { data, isPending } = useQuery({
    queryKey: ['mails-date', applied],
    enabled: !!applied,
    queryFn: async () => (await mailsApi.list(params as never)) as { data: Entity[] },
  });

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Courriers par date" description="Sélection d'une période pour lister les courriers." />
      <div className="flex w-full flex-wrap items-end gap-3 rounded border border-border bg-surface p-4">
        <label className="flex flex-col gap-1 text-sm">Du
          <input type="date" value={from} onChange={(e) => setFrom(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
        </label>
        <label className="flex flex-col gap-1 text-sm">Au
          <input type="date" value={to} onChange={(e) => setTo(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
        </label>
        <button
          type="button"
          onClick={() => setApplied({ from, to })}
          className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90"
        >
          Lister
        </button>
      </div>
      {applied && (
        <div className="min-h-0 flex-1 overflow-auto rounded border border-border">
          <table className="w-full text-left text-sm">
            <thead className="sticky top-0 bg-surface">
              <tr>
                <th className="px-3 py-2 font-medium text-muted-foreground">Code</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">Nom</th>
                <th className="px-3 py-2 font-medium text-muted-foreground">Date</th>
              </tr>
            </thead>
            <tbody>
              {isPending && <tr><td colSpan={3} className="px-3 py-6 text-center text-muted-foreground">Chargement…</td></tr>}
              {(data?.data ?? []).map((m) => (
                <tr key={String(m.id)} className="border-t border-border/60 hover:bg-muted">
                  <td className="px-3 py-2 font-mono text-xs">{String(m.code ?? '')}</td>
                  <td className="px-3 py-2">{String(m.name ?? '')}</td>
                  <td className="px-3 py-2">{m.date ? new Date(String(m.date)).toLocaleDateString('fr-FR') : '—'}</td>
                </tr>
              ))}
              {!isPending && (data?.data ?? []).length === 0 && (
                <tr><td colSpan={3} className="px-3 py-6 text-center text-muted-foreground">Aucun courrier sur cette période.</td></tr>
              )}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

function Field({ label, value, onChange, type = 'text' }: { label: string; value: string; onChange: (v: string) => void; type?: string }) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      {label}
      <input type={type} value={value} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
    </label>
  );
}
