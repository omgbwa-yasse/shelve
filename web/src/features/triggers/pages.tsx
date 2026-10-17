'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { TableColumn } from '@/components/ui/table';
import { DataTable, Pagination } from '@/components/ui/table';
import { useDestroy, useResource, useResourceList, useCreate, useUpdate } from '@/lib/api/hooks';
import type { Entity } from '@/lib/api/types';
import type { FeatureRoute } from '@/lib/routing';
import * as api from './services/trigger.service';
import {
  ASSIGNMENT_LABELS,
  EVENT_OPTIONS,
  OPERATOR_LABELS,
  runTrigger,
  getTriggerRuns,
  triggersApi,
  type TriggerCondition,
  type TriggerEntity,
  type TriggerScope,
} from './services/trigger.service';

/* ------------------------------------------------------------------ */
/* Constantes d'affichage                                              */
/* ------------------------------------------------------------------ */
const EVENT_LABELS: Record<string, string> = Object.fromEntries(EVENT_OPTIONS.map((o) => [o.value, o.label]));
const MODE_LABELS: Record<string, string> = { if: 'Si toutes les conditions', unless: 'Sauf si', both: 'Si ET sauf si' };
const ACTION_LABELS: Record<string, string> = { create_task: 'Créer une tâche', start_workflow: 'Démarrer un workflow' };

function fmt(v: unknown): string {
  if (v === null || v === undefined) return '—';
  return String(v);
}

/* ------------------------------------------------------------------ */
/* Liste des déclencheurs                                              */
/* ------------------------------------------------------------------ */
function TriggerList() {
  const [page, setPage] = useState(1);
  const { data, isLoading, isError } = useResourceList(triggersApi, 'triggers', { page, 'page.size': 20 } as never);
  const destroy = useDestroy(triggersApi, 'triggers');
  const rows = (data?.data ?? []) as TriggerEntity[];
  const m = data?.meta;

  const COLS: TableColumn<TriggerEntity>[] = [
    { key: 'name', label: 'Nom', render: (r) => <Link href={`/workflow/triggers/${r.id}`} className="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">{r.name}</Link> },
    { key: 'event_type', label: 'Événement', render: (r) => fmt(EVENT_LABELS[r.event_type] ?? r.event_type) },
    { key: 'action_type', label: 'Action', render: (r) => fmt(ACTION_LABELS[r.action_type] ?? r.action_type) },
    { key: 'is_enabled', label: 'Actif', render: (r) => (r.is_enabled ? 'Oui' : 'Non') },
    { key: 'run_count', label: 'Exécutions', render: (r) => fmt(r.run_count) },
    { key: 'last_run_at', label: 'Dernier déclenchement', render: (r) => (r.last_run_at ? new Date(String(r.last_run_at)).toLocaleString('fr-FR') : '—') },
  ];

  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h1 className="text-xl font-semibold">Déclencheurs</h1>
          <p className="mt-1 text-sm text-muted-foreground">
            Automatisations « si… alors… » : un événement sur les notices crée des tâches, sans action manuelle.
          </p>
        </div>
        <Link href="/workflow/triggers/create" className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">+ Nouveau déclencheur</Link>
      </header>
      <DataTable
        columns={COLS}
        rows={rows}
        loading={isLoading}
        error={isError}
        emptyLabel="Aucun déclencheur."
        actions={(row) => (
          <button
            type="button"
            onClick={() => { if (window.confirm('Supprimer ce déclencheur ?')) destroy.mutate(row.id); }}
            className="rounded border border-border px-2 py-1 text-xs text-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >Supprimer</button>
        )}
      />
      <Pagination page={page} totalPages={m?.last_page ?? 1} total={m?.total} onChange={setPage} />
    </div>
  );
}

/* ------------------------------------------------------------------ */
/* Détail : configuration + journal + exécution manuelle               */
/* ------------------------------------------------------------------ */
function TriggerDetail({ id }: { id: string }) {
  const { data, isLoading } = useResource(triggersApi, 'triggers', id);
  const router = useRouter();
  const destroy = useDestroy(triggersApi, 'triggers');
  const [runs, setRuns] = useState<Record<string, unknown>[]>([]);
  const [recordId, setRecordId] = useState('');
  const [result, setResult] = useState<string | null>(null);
  const [running, setRunning] = useState(false);

  useEffect(() => {
    if (id) getTriggerRuns(Number(id)).then((r) => setRuns((r.data ?? []) as Record<string, unknown>[])).catch(() => setRuns([]));
  }, [id]);

  if (isLoading) return <p className="text-sm text-muted-foreground">Chargement…</p>;
  const t = (data?.data ?? {}) as TriggerEntity;

  async function handleRun(e: React.FormEvent) {
    e.preventDefault();
    if (!recordId) return;
    setRunning(true);
    setResult(null);
    try {
      const r = await runTrigger(t.id, recordId);
      setResult(r.data.matched ? `Déclenché : ${r.data.status}` : 'Non déclenché (portée ou conditions non remplies).');
      const fresh = await getTriggerRuns(t.id);
      setRuns((fresh.data ?? []) as Record<string, unknown>[]);
    } catch (err) {
      setResult(`Erreur : ${err instanceof Error ? err.message : 'inconnue'}`);
    } finally {
      setRunning(false);
    }
  }

  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <p className="font-mono text-xs text-muted-foreground">Déclencheur #{t.id}</p>
          <h1 className="text-xl font-semibold">{fmt(t.name)}</h1>
        </div>
        <div className="flex gap-2">
          <Link href={`/workflow/triggers/${id}/edit`} className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Modifier</Link>
          <button
            type="button"
            onClick={() => { if (window.confirm('Supprimer ?')) { destroy.mutate(id); router.push('/workflow/triggers'); } }}
            className="rounded border border-danger/40 bg-danger/10 px-3 py-1.5 text-sm text-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >Supprimer</button>
        </div>
      </header>

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div className="rounded border border-border bg-surface p-4">
          <h2 className="mb-3 text-sm font-semibold">Configuration</h2>
          <dl className="space-y-2 text-sm">
            <div className="flex justify-between gap-4"><dt className="text-muted-foreground">Événement</dt><dd>{fmt(EVENT_LABELS[t.event_type] ?? t.event_type)}</dd></div>
            <div className="flex justify-between gap-4"><dt className="text-muted-foreground">Conditions</dt><dd>{fmt(MODE_LABELS[t.condition_mode] ?? t.condition_mode)}</dd></div>
            <div className="flex justify-between gap-4"><dt className="text-muted-foreground">Action</dt><dd>{fmt(ACTION_LABELS[t.action_type] ?? t.action_type)}</dd></div>
            <div className="flex justify-between gap-4"><dt className="text-muted-foreground">Priorité</dt><dd>{fmt(t.action_config?.priority ?? 'normal')}</dd></div>
            <div className="flex justify-between gap-4"><dt className="text-muted-foreground">Échéance</dt><dd>{t.action_config?.due_days ? `${t.action_config.due_days} jour(s) ouvré(s)` : '—'}</dd></div>
            <div className="flex justify-between gap-4"><dt className="text-muted-foreground">Assignation</dt><dd>{fmt(ASSIGNMENT_LABELS[t.action_config?.assignment_type ?? ''] ?? t.action_config?.assignment_type ?? '—')}</dd></div>
            <div className="flex justify-between gap-4"><dt className="text-muted-foreground">Actif</dt><dd>{t.is_enabled ? 'Oui' : 'Non'}</dd></div>
            <div className="flex justify-between gap-4"><dt className="text-muted-foreground">En arrière-plan</dt><dd>{t.run_in_background ? 'Oui' : 'Non'}</dd></div>
          </dl>
          {(t.conditions?.length ?? 0) > 0 && (
            <div className="mt-3 border-t border-border pt-3">
              <p className="mb-2 text-xs font-semibold uppercase text-muted-foreground">Conditions (Si)</p>
              {t.conditions?.map((c, i) => <p key={i} className="text-sm">{fmt(c.field)} {fmt(OPERATOR_LABELS[c.operator] ?? c.operator)} {fmt(c.value)}</p>)}
            </div>
          )}
          {(t.unless_conditions?.length ?? 0) > 0 && (
            <div className="mt-3 border-t border-border pt-3">
              <p className="mb-2 text-xs font-semibold uppercase text-muted-foreground">Conditions (Sauf si)</p>
              {t.unless_conditions?.map((c, i) => <p key={i} className="text-sm">{fmt(c.field)} {fmt(OPERATOR_LABELS[c.operator] ?? c.operator)} {fmt(c.value)}</p>)}
            </div>
          )}
        </div>

        <div className="rounded border border-border bg-surface p-4">
          <h2 className="mb-3 text-sm font-semibold">Exécution manuelle (Test)</h2>
          <form onSubmit={handleRun} className="flex gap-2">
            <input
              type="text"
              value={recordId}
              onChange={(e) => setRecordId(e.target.value)}
              placeholder="ID de la notice (record)"
              className="w-full rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
            />
            <button type="submit" disabled={running || !recordId} className="shrink-0 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              {running ? '…' : 'Tester'}
            </button>
          </form>
          {result && <p className="mt-2 text-sm text-muted-foreground">{result}</p>}
          <h3 className="mb-2 mt-5 text-sm font-semibold">Journal d'exécution</h3>
          {runs.length === 0 ? (
            <p className="text-sm text-muted-foreground">Aucune exécution pour le moment.</p>
          ) : (
            <DataTable
              columns={[
                { key: 'executed_at', label: 'Date', render: (r) => (r.executed_at ? new Date(String(r.executed_at)).toLocaleString('fr-FR') : '—') },
                { key: 'event_type', label: 'Événement' },
                { key: 'matched', label: 'Match', render: (r) => (r.matched ? 'Oui' : 'Non') },
                { key: 'status', label: 'Statut', render: (r) => <span className={String(r.status) === 'success' ? 'text-green-600' : String(r.status) === 'failed' ? 'text-danger' : 'text-muted-foreground'}>{fmt(r.status)}</span> },
                { key: 'triggerable_id', label: 'Notice', render: (r) => fmt(r.triggerable_id) },
              ]}
              rows={runs as Entity[]}
              loading={false}
              emptyLabel="Aucune exécution."
            />
          )}
        </div>
      </div>
    </div>
  );
}

/* ------------------------------------------------------------------ */
/* Formulaire création / édition                                       */
/* ------------------------------------------------------------------ */
const FIELDS = ['record.name', 'record.code', 'record.status_id', 'record.activity_id', 'record.type_id', 'record.workplace_id', 'metadata.any'];

function TriggerForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const create = useCreate(triggersApi, 'triggers');
  const update = useUpdate(triggersApi, 'triggers');
  const { data } = useResource(triggersApi, 'triggers', id);

  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [eventType, setEventType] = useState('record.created');
  const [modeC, setModeC] = useState<'if' | 'unless' | 'both'>('if');
  const [conditions, setConditions] = useState<TriggerCondition[]>([]);
  const [unlessConditions, setUnlessConditions] = useState<TriggerCondition[]>([]);
  const [actionType, setActionType] = useState('create_task');
  const [titleTemplate, setTitleTemplate] = useState('');
  const [descriptionTemplate, setDescriptionTemplate] = useState('');
  const [priority, setPriority] = useState('normal');
  const [dueDays, setDueDays] = useState('');
  const [assignmentType, setAssignmentType] = useState('creator');
  const [assignmentValue, setAssignmentValue] = useState('');
  const [scope, setScope] = useState<TriggerScope>({ recursive: false });

  useEffect(() => {
    if (mode === 'edit' && data?.data && name === '') {
      const t = data.data as TriggerEntity;
      setName(t.name ?? '');
      setDescription(t.description ?? '');
      setEventType(t.event_type);
      setModeC(t.condition_mode ?? 'if');
      setConditions(t.conditions ?? []);
      setUnlessConditions(t.unless_conditions ?? []);
      setActionType(t.action_type ?? 'create_task');
      setTitleTemplate(t.action_config?.title_template ?? '');
      setDescriptionTemplate(t.action_config?.description_template ?? '');
      setPriority(t.action_config?.priority ?? 'normal');
      setDueDays(t.action_config?.due_days ? String(t.action_config.due_days) : '');
      setAssignmentType(t.action_config?.assignment_type ?? 'creator');
      setAssignmentValue(t.action_config?.assignment_value !== undefined && t.action_config?.assignment_value !== null ? String(t.action_config.assignment_value) : '');
      setScope(t.scope ?? { recursive: false });
    }
  }, [data, mode, name]);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    const payload: Record<string, unknown> = {
      name,
      description,
      event_type: eventType,
      condition_mode: modeC,
      conditions: conditions.length ? conditions : null,
      unless_conditions: unlessConditions.length ? unlessConditions : null,
      scope,
      action_type: actionType,
      action_config: {
        title_template: titleTemplate,
        description_template: descriptionTemplate,
        priority,
        due_days: dueDays ? Number(dueDays) : null,
        assignment_type: assignmentType,
        assignment_value: assignmentValue || null,
      },
      run_in_background: true,
      is_enabled: true,
    };

    try {
      if (mode === 'edit' && id) await update.mutateAsync({ id, payload });
      else await create.mutateAsync(payload);
      router.push('/workflow/triggers');
    } catch (err) {
      // @ts-expect-error — erreur API 422
      window.alert(err?.message ?? 'Erreur lors de l\'enregistrement.');
    }
  }

  function updateCondition(list: 'conditions' | 'unlessConditions', i: number, patch: Partial<TriggerCondition>) {
    const setter = list === 'conditions' ? setConditions : setUnlessConditions;
    setter((prev) => prev.map((c, idx) => (idx === i ? { ...c, ...patch } : c)));
  }

  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{mode === 'edit' ? 'Modifier : déclencheur' : 'Nouveau déclencheur'}</h1>
        <button type="button" onClick={() => router.push('/workflow/triggers')} className="rounded border border-border px-3 py-1.5 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Annuler</button>
      </header>

      <div className="grid grid-cols-1 gap-4 rounded border border-border bg-surface p-4 lg:grid-cols-2">
        <Label label="Nom *">
          <input value={name} onChange={(e) => setName(e.target.value)} required className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" />
        </Label>
        <Label label="Description">
          <textarea value={description} onChange={(e) => setDescription(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" rows={2} />
        </Label>
      </div>

      {/* Événement */}
      <section className="rounded border border-border bg-surface p-4">
        <h2 className="mb-3 text-sm font-semibold">1. Événement (quand ?)</h2>
        <Label label="Événement déclencheur">
          <select value={eventType} onChange={(e) => setEventType(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
            {EVENT_OPTIONS.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
        </Label>
      </section>

      {/* Conditions */}
      <section className="rounded border border-border bg-surface p-4">
        <h2 className="mb-3 text-sm font-semibold">2. Conditions (si ?)</h2>
        <Label label="Mode">
          <select value={modeC} onChange={(e) => setModeC(e.target.value as 'if' | 'unless' | 'both')} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
            <option value="if">Si toutes les conditions sont remplies</option>
            <option value="unless">Sauf si les conditions sont remplies</option>
            <option value="both">Si… sauf si… (les deux)</option>
          </select>
        </Label>

        <ConditionEditor
          title="Bloc « Si »"
          items={conditions}
          onChange={setConditions}
          onUpdate={(i, p) => updateCondition('conditions', i, p)}
        />
        {modeC === 'both' && (
          <ConditionEditor
            title="Bloc « Sauf si »"
            items={unlessConditions}
            onChange={setUnlessConditions}
            onUpdate={(i, p) => updateCondition('unlessConditions', i, p)}
          />
        )}

        <div className="mt-4">
          <p className="mb-2 text-sm font-semibold">Portée (optionnelle — dossiers/classeurs ciblés)</p>
          <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <Label label="Identifiants d'espaces de travail (workplace_ids)">
              <input
                value={(scope.workplace_ids ?? []).join(',')}
                onChange={(e) => setScope((p) => ({ ...p, workplace_ids: e.target.value ? e.target.value.split(',').map((x) => Number(x.trim())).filter(Boolean) : [] }))}
                className="w-full rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                placeholder="1,2,3"
              />
            </Label>
            <Label label="Identifiants de classeurs (activity_ids)">
              <input
                value={(scope.activity_ids ?? []).join(',')}
                onChange={(e) => setScope((p) => ({ ...p, activity_ids: e.target.value ? e.target.value.split(',').map((x) => Number(x.trim())).filter(Boolean) : [] }))}
                className="w-full rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                placeholder="1,2,3"
              />
            </Label>
            <label className="flex items-center gap-2 text-sm">
              <input type="checkbox" checked={scope.recursive ?? false} onChange={(e) => setScope((p) => ({ ...p, recursive: e.target.checked }))} />
              Appliquer aussi aux sous-classeurs (comme Alfresco « subfolders »)
            </label>
          </div>
        </div>
      </section>

      {/* Action */}
      <section className="rounded border border-border bg-surface p-4">
        <h2 className="mb-3 text-sm font-semibold">3. Action (donc ?)</h2>
        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <Label label="Type d'action">
            <select value={actionType} onChange={(e) => setActionType(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
              <option value="create_task">Créer une tâche</option>
              <option value="start_workflow">Démarrer un workflow</option>
            </select>
          </Label>
          <Label label="Priorité de la tâche">
            <select value={priority} onChange={(e) => setPriority(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
              <option value="low">Basse</option>
              <option value="normal">Normale</option>
              <option value="high">Haute</option>
              <option value="urgent">Urgente</option>
            </select>
          </Label>
          <Label label="Titre de la tâche (template, ex. Traiter {{record.code}})">
            <input value={titleTemplate} onChange={(e) => setTitleTemplate(e.target.value)} required={actionType === 'create_task'} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" />
          </Label>
          <Label label="Description (template)">
            <input value={descriptionTemplate} onChange={(e) => setDescriptionTemplate(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" />
          </Label>
          <Label label="Échéance (jours ouvrés)">
            <input type="number" min={0} value={dueDays} onChange={(e) => setDueDays(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" />
          </Label>
          <Label label="Assigner à">
            <select value={assignmentType} onChange={(e) => setAssignmentType(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
              {Object.entries(ASSIGNMENT_LABELS).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
            </select>
          </Label>
          {(assignmentType === 'user' || assignmentType === 'role' || assignmentType === 'unit') && (
            <Label label={assignmentType === 'user' ? 'ID de l\'utilisateur' : assignmentType === 'role' ? 'Nom du rôle' : 'ID de l\'espace de travail (unité)'}>
              <input value={assignmentValue} onChange={(e) => setAssignmentValue(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary" />
            </Label>
          )}
        </div>
        <p className="mt-3 text-xs text-muted-foreground">
          Modèles disponibles : <code className="rounded bg-muted px-1">{"{{record.name}}"}</code>,{' '}
          <code className="rounded bg-muted px-1">{"{{record.code}}"}</code>,{' '}
          <code className="rounded bg-muted px-1">{"{{record.status_id}}"}</code>,{' '}
          <code className="rounded bg-muted px-1">{"{{metadata.…}}"}</code>
        </p>
      </section>

      <footer className="flex justify-end">
        <button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">Enregistrer</button>
      </footer>
    </form>
  );
}

function ConditionEditor({
  title,
  items,
  onChange,
  onUpdate,
}: {
  title: string;
  items: TriggerCondition[];
  onChange: (items: TriggerCondition[]) => void;
  onUpdate: (i: number, patch: Partial<TriggerCondition>) => void;
}) {
  return (
    <div className="mt-4">
      <p className="mb-2 text-sm font-semibold">{title}</p>
      {items.length === 0 && <p className="mb-2 text-sm text-muted-foreground">Aucune condition — le déclencheur s'applique à tous les événements.</p>}
      <div className="space-y-2">
        {items.map((c, i) => (
          <div key={i} className="flex flex-wrap items-center gap-2">
            <select value={c.field} onChange={(e) => onUpdate(i, { field: e.target.value })} className="w-auto rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
              {FIELDS.map((f) => <option key={f} value={f}>{f}</option>)}
            </select>
            <select value={c.operator} onChange={(e) => onUpdate(i, { operator: e.target.value as TriggerCondition['operator'] })} className="w-auto rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary">
              {Object.entries(OPERATOR_LABELS).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
            </select>
            {!['is_null', 'not_null'].includes(c.operator) && (
              <input
                value={c.value ?? ''}
                onChange={(e) => onUpdate(i, { value: e.target.value })}
                className="w-40 rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
                placeholder="valeur"
              />
            )}
            <button
              type="button"
              onClick={() => onChange(items.filter((_, idx) => idx !== i))}
              className="rounded border border-border px-2 py-1 text-xs text-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >Retirer</button>
          </div>
        ))}
      </div>
      <button
        type="button"
        onClick={() => onChange([...items, { field: 'record.name', operator: 'contains', value: '' }])}
        className="mt-2 rounded border border-border px-2 py-1 text-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
      >+ Ajouter une condition</button>
    </div>
  );
}

function Label({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>{label}</span>
      {children}
    </label>
  );
}

/* ------------------------------------------------------------------ */
/* Routes de la feature Déclencheurs                                   */
/* ------------------------------------------------------------------ */
export const routes: FeatureRoute[] = [
  { path: '/workflow/triggers', List: TriggerList, Detail: TriggerDetail, Form: TriggerForm },
];
