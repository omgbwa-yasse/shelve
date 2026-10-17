/**
 * Feature Déclencheurs — automation « If This Then That » (PLAN-DECLENCHEURS-TACHES.md).
 * Consomme `routes/api/Triggers.php`.
 */
import { apiFetch } from '@/lib/api/client';
import { createResourceApi } from '@/lib/api/resources';
import type { Entity, ItemEnvelope, ListEnvelope } from '@/lib/api/types';

export const triggersApi = createResourceApi('triggers');

export type TriggerCondition = {
  field: string;
  operator: 'equals' | 'not_equals' | 'contains' | 'not_contains' | 'greater_than' | 'less_than' | 'is_null' | 'not_null' | 'starts_with' | 'regex';
  value?: string | number | null;
};

export type TriggerScope = {
  workplace_ids?: number[];
  activity_ids?: number[];
  record_type_ids?: number[];
  keyword_ids?: number[];
  recursive?: boolean;
};

export type TriggerActionConfig = {
  title_template?: string;
  description_template?: string;
  priority?: 'low' | 'normal' | 'high' | 'urgent';
  due_days?: number;
  assignment_type?: 'user' | 'role' | 'unit' | 'creator' | 'manager' | 'previous';
  assignment_value?: string | number | null;
  workflow_definition_id?: number;
};

export type TriggerEntity = Entity & {
  id: number;
  organisation_id: number;
  name: string;
  description?: string | null;
  event_type: string;
  condition_mode: 'if' | 'unless' | 'both';
  conditions?: TriggerCondition[];
  unless_conditions?: TriggerCondition[];
  scope?: TriggerScope | null;
  action_type: string;
  action_config?: TriggerActionConfig | null;
  run_in_background: boolean;
  is_enabled: boolean;
  sort_order: number;
  run_count: number;
  last_run_at?: string | null;
  created_by?: number | null;
  created_at?: string;
  updated_at?: string;
};

export const EVENT_OPTIONS: { value: string; label: string }[] = [
  { value: 'record.created', label: 'Une notice / un dossier est créé' },
  { value: 'record.status_changed', label: 'Le statut d\'une notice change' },
  { value: 'record.uploaded', label: 'Un fichier est téléversé' },
  { value: 'record.keyword_added', label: 'Un mot-clé est ajouté' },
  { value: 'record.transferred', label: 'Un fichier est transféré' },
  { value: 'task.created', label: 'Une tâche est créée' },
];

export const OPERATOR_LABELS: Record<string, string> = {
  equals: 'égal à',
  not_equals: 'différent de',
  contains: 'contient',
  not_contains: 'ne contient pas',
  greater_than: 'supérieur à',
  less_than: 'inférieur à',
  is_null: 'est vide',
  not_null: 'n\'est pas vide',
  starts_with: 'commence par',
  regex: 'correspond à (regex)',
};

export const ASSIGNMENT_LABELS: Record<string, string> = {
  user: 'Une personne',
  role: 'Un rôle / une fonction',
  unit: 'Une unité (espace de travail)',
  creator: 'L\'auteur de l\'événement',
  manager: 'Le responsable de la notice',
  previous: 'L\'auteur de la tâche précédente',
};

/** GET /api/v1/triggers/{id}/run — exécution manuelle sur un record (Run Rules). */
export function runTrigger(triggerId: number, recordId: number | string) {
  return apiFetch<{ data: { matched: boolean; status: string; run: Record<string, unknown> | null } }>(
    `/api/v1/triggers/${triggerId}/run`,
    { method: 'POST', body: { record_id: recordId } },
  );
}

/** GET /api/v1/triggers/{id}/runs — journal d'exécution. */
export function getTriggerRuns(triggerId: number) {
  return apiFetch<ListEnvelope<Record<string, unknown>>>(`/api/v1/triggers/${triggerId}/runs`);
}
