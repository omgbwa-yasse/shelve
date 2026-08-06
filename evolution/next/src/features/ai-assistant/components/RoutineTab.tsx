'use client';

import { useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { Icon } from '@/components/icons';
import type { Entity } from '@/lib/api/types';
import { useResourceList, useCreate, useDestroy } from '@/lib/api/hooks';
import { promptsApi, aiSkillsApi } from '@/features/ai/services/ai.service';
import { routinesApi, runRoutine } from '@/features/ai-assistant/services/assistant.service';
import { useSessionPermissions } from '@/features/auth/context';
import { can } from '@/lib/permissions';

const SCHEDULE_LABELS: Record<string, string> = {
  once: 'Une fois',
  hourly: 'Toutes les heures',
  daily: 'Chaque jour',
  weekly: 'Chaque semaine',
};

const DAYS = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

const STATUS_STYLES: Record<string, string> = {
  success: 'text-success',
  error: 'text-danger',
};

/** Routines programmées : exécutent un prompt ou un skill IA (D14) à intervalle régulier. */
export function RoutineTab() {
  const queryClient = useQueryClient();
  const permissions = useSessionPermissions();
  const canView = can(permissions, 'ai_routine_viewAny');
  const canCreate = can(permissions, 'ai_routine_create');
  const canRun = can(permissions, 'ai_routine_update');
  const canDelete = can(permissions, 'ai_routine_delete');

  const { data } = useResourceList(routinesApi, 'ai-routines', {}, canView);
  const { data: prompts } = useResourceList(promptsApi, 'prompts', { 'page.size': 100 } as never);
  const { data: skills } = useResourceList(aiSkillsApi, 'ai-skills', { 'page.size': 100 } as never);
  const create = useCreate(routinesApi, 'ai-routines');
  const destroy = useDestroy(routinesApi, 'ai-routines');
  const [runningId, setRunningId] = useState<string | null>(null);

  const routines = (data?.data ?? []) as Entity[];
  const promptOptions = (prompts?.data ?? []) as Entity[];
  const skillOptions = (skills?.data ?? []) as Entity[];

  const [form, setForm] = useState({
    name: '',
    source: '' as string, // "prompt:<id>" | "skill:<id>"
    schedule_type: 'daily',
    run_time: '08:00',
    day_of_week: '1',
  });

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (!form.name || !form.source) return;
    if (!window.confirm(`Créer la routine « ${form.name} » ? Elle exécutera automatiquement une action IA selon la fréquence choisie.`)) return;

    const [kind, id] = form.source.split(':');
    const payload: Record<string, unknown> = {
      name: form.name,
      schedule_type: form.schedule_type,
      prompt_id: kind === 'prompt' ? id : undefined,
      skill_id: kind === 'skill' ? id : undefined,
    };
    if (form.schedule_type === 'daily' || form.schedule_type === 'weekly') {
      payload.run_time = form.run_time;
    }
    if (form.schedule_type === 'weekly') {
      payload.day_of_week = form.day_of_week;
    }

    await create.mutateAsync(payload);
    setForm({ name: '', source: '', schedule_type: 'daily', run_time: '08:00', day_of_week: '1' });
  }

  async function run(id: string, name: string) {
    if (!window.confirm(`Exécuter la routine « ${name} » maintenant ?`)) return;

    setRunningId(id);
    try {
      await runRoutine(id);
      queryClient.invalidateQueries({ queryKey: ['ai-routines'] });
    } finally {
      setRunningId(null);
    }
  }

  function remove(id: string | number | undefined, name: string) {
    if (!window.confirm(`Supprimer la routine « ${name} » ?`)) return;
    destroy.mutate(id);
  }

  if (!canView) {
    return (
      <div className="flex h-full items-start p-3">
        <p className="text-sm text-muted-foreground">Votre profil ne dispose pas de la permission nécessaire pour voir les routines IA.</p>
      </div>
    );
  }

  return (
    <div className="flex h-full flex-col overflow-y-auto p-3">
      {routines.length === 0 ? (
        <p className="text-sm text-muted-foreground">Aucune routine programmée.</p>
      ) : (
        <ul className="flex flex-col gap-2">
          {routines.map((r) => (
            <li key={String(r.id)} className="rounded border border-border p-2 text-sm">
              <div className="flex items-center justify-between gap-2">
                <span className="font-medium">{String(r.name)}</span>
                <div className="flex items-center gap-1">
                  {canRun && (
                    <button
                      type="button"
                      onClick={() => run(String(r.id), String(r.name))}
                      disabled={runningId === String(r.id)}
                      className="rounded border border-border px-2 py-0.5 text-xs hover:bg-muted disabled:opacity-50"
                    >
                      {runningId === String(r.id) ? '…' : 'Exécuter'}
                    </button>
                  )}
                  {canDelete && (
                    <button
                      type="button"
                      onClick={() => remove(r.id, String(r.name))}
                      className="rounded p-1 text-muted-foreground hover:bg-danger/10 hover:text-danger"
                      aria-label="Supprimer"
                    >
                      <Icon name="trash" className="h-3.5 w-3.5" />
                    </button>
                  )}
                </div>
              </div>
              <p className="mt-1 text-xs text-muted-foreground">
                {SCHEDULE_LABELS[String(r.schedule_type)] ?? String(r.schedule_type)}
                {r.run_time ? ` à ${String(r.run_time)}` : ''}
                {r.schedule_type === 'weekly' && r.day_of_week !== null && r.day_of_week !== undefined
                  ? ` (${DAYS[Number(r.day_of_week)]})`
                  : ''}
              </p>
              {r.last_status ? (
                <p className={`mt-1 text-xs ${STATUS_STYLES[String(r.last_status)] ?? ''}`}>
                  Dernière exécution : {r.last_output ? String(r.last_output) : String(r.last_status)}
                </p>
              ) : null}
            </li>
          ))}
        </ul>
      )}

      {canCreate ? (
      <form onSubmit={submit} className="mt-3 flex flex-col gap-2 border-t border-border pt-3 text-sm">
        <label className="flex flex-col gap-1">
          <span>Nom</span>
          <input
            value={form.name}
            onChange={(e) => setForm((p) => ({ ...p, name: e.target.value }))}
            required
            className="rounded border border-border bg-background px-2 py-1.5"
          />
        </label>
        <label className="flex flex-col gap-1">
          <span>Prompt ou skill à exécuter</span>
          <select
            value={form.source}
            onChange={(e) => setForm((p) => ({ ...p, source: e.target.value }))}
            required
            className="rounded border border-border bg-background px-2 py-1.5"
          >
            <option value="">—</option>
            {promptOptions.map((p) => (
              <option key={`prompt:${p.id}`} value={`prompt:${p.id}`}>Prompt — {String(p.title)}</option>
            ))}
            {skillOptions.map((s) => (
              <option key={`skill:${s.id}`} value={`skill:${s.id}`}>Skill — {String(s.name)}</option>
            ))}
          </select>
        </label>
        <label className="flex flex-col gap-1">
          <span>Fréquence</span>
          <select
            value={form.schedule_type}
            onChange={(e) => setForm((p) => ({ ...p, schedule_type: e.target.value }))}
            className="rounded border border-border bg-background px-2 py-1.5"
          >
            {Object.entries(SCHEDULE_LABELS).map(([value, label]) => (
              <option key={value} value={value}>{label}</option>
            ))}
          </select>
        </label>
        {(form.schedule_type === 'daily' || form.schedule_type === 'weekly') && (
          <label className="flex flex-col gap-1">
            <span>Heure</span>
            <input
              type="time"
              value={form.run_time}
              onChange={(e) => setForm((p) => ({ ...p, run_time: e.target.value }))}
              className="rounded border border-border bg-background px-2 py-1.5"
            />
          </label>
        )}
        {form.schedule_type === 'weekly' && (
          <label className="flex flex-col gap-1">
            <span>Jour</span>
            <select
              value={form.day_of_week}
              onChange={(e) => setForm((p) => ({ ...p, day_of_week: e.target.value }))}
              className="rounded border border-border bg-background px-2 py-1.5"
            >
              {DAYS.map((day, i) => (
                <option key={day} value={i}>{day}</option>
              ))}
            </select>
          </label>
        )}
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-primary-foreground">Créer la routine</button>
      </form>
      ) : (
        <p className="mt-3 border-t border-border pt-3 text-xs text-muted-foreground">
          Votre profil ne dispose pas de la permission nécessaire pour créer une routine.
        </p>
      )}
    </div>
  );
}
