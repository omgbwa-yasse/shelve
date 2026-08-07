/**
 * Feature Projets — Projet / Tâche / OKR / KPI (D17). Voir
 * `evolution/PROJECT-OKR-KPI-PLAN.md`.
 *
 * Le rattachement (`attachable_type`/`attachable_id`) est un alias court côté
 * API ("workplace" | "organisation" | "user") — jamais un FQCN PHP.
 */
import { apiFetch } from '@/lib/api/client';
import { createResourceApi, toQuery } from '@/lib/api/resources';
import type { Entity, ItemEnvelope, ListEnvelope } from '@/lib/api/types';

export const projectsApi = createResourceApi('projects');
export const objectivesApi = createResourceApi('objectives');
export const kpisApi = createResourceApi('kpis');
/** Utilisé uniquement pour update()/destroy() — la création passe par `createKeyResult`. */
export const keyResultsApi = createResourceApi('key-results');

export type AttachableAlias = 'workplace' | 'organisation' | 'user';

export const ATTACHABLE_LABELS: Record<AttachableAlias, string> = {
  workplace: 'Espace de travail',
  organisation: 'Unité administrative',
  user: 'Personne',
};

/** GET /api/v1/projects/{id}/tasks — pas de ResourceApi générique pour une sous-liste. */
export function getProjectTasks(projectId: string | number) {
  return apiFetch<ListEnvelope<Entity>>(`/api/v1/projects/${projectId}/tasks`);
}

/** POST /api/v1/objectives/{id}/key-results — réutilise `action()` (id + verbe). */
export function createKeyResult(objectiveId: string | number, payload: Record<string, unknown>) {
  return objectivesApi.action(objectiveId, 'key-results', payload) as Promise<ItemEnvelope<Entity>>;
}

/** GET /api/v1/kpis/{id}/measurements?from=&to= */
export function getKpiMeasurements(kpiId: string | number, params?: { from?: string; to?: string }) {
  return apiFetch<ListEnvelope<Entity>>(`/api/v1/kpis/${kpiId}/measurements${toQuery(params)}`);
}

/** POST /api/v1/kpis/{id}/measurements */
export function recordKpiMeasurement(kpiId: string | number, payload: { value: number; measured_at?: string }) {
  return kpisApi.action(kpiId, 'measurements', payload) as Promise<ItemEnvelope<Entity>>;
}

// ---------------------------------------------------------------------------
// Extension MS-Project-parity : jalons, livrables, ressources, rapports
// d'étape, dépendances entre tâches, alertes calculées. Voir
// `evolution/PROJECT-OKR-KPI-PLAN.md` (extension).
// ---------------------------------------------------------------------------

export const RESOURCE_TYPE_LABELS: Record<string, string> = {
  human: 'Humaine',
  financial: 'Financière',
  material: 'Matérielle',
  informational: 'Informationnelle',
};

export const milestonesApi = createResourceApi('project-milestones');
export const deliverablesApi = createResourceApi('project-deliverables');
export const resourcesApi = createResourceApi('project-resources');

/** GET /api/v1/projects/{id}/milestones */
export function getProjectMilestones(projectId: string | number) {
  return apiFetch<ListEnvelope<Entity>>(`/api/v1/projects/${projectId}/milestones`);
}

/** POST /api/v1/projects/{id}/milestones */
export function createProjectMilestone(projectId: string | number, payload: Record<string, unknown>) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/projects/${projectId}/milestones`, { method: 'POST', body: payload });
}

/** POST /api/v1/project-milestones/{id}/reach */
export function reachProjectMilestone(milestoneId: string | number) {
  return milestonesApi.action(milestoneId, 'reach', {}) as Promise<ItemEnvelope<Entity>>;
}

/** GET /api/v1/projects/{id}/deliverables */
export function getProjectDeliverables(projectId: string | number) {
  return apiFetch<ListEnvelope<Entity>>(`/api/v1/projects/${projectId}/deliverables`);
}

/** POST /api/v1/projects/{id}/deliverables */
export function createProjectDeliverable(projectId: string | number, payload: Record<string, unknown>) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/projects/${projectId}/deliverables`, { method: 'POST', body: payload });
}

export function submitProjectDeliverable(deliverableId: string | number) {
  return deliverablesApi.action(deliverableId, 'submit', {}) as Promise<ItemEnvelope<Entity>>;
}

export function approveProjectDeliverable(deliverableId: string | number) {
  return deliverablesApi.action(deliverableId, 'approve', {}) as Promise<ItemEnvelope<Entity>>;
}

export function rejectProjectDeliverable(deliverableId: string | number) {
  return deliverablesApi.action(deliverableId, 'reject', {}) as Promise<ItemEnvelope<Entity>>;
}

/** GET /api/v1/projects/{id}/resources */
export function getProjectResources(projectId: string | number) {
  return apiFetch<ListEnvelope<Entity>>(`/api/v1/projects/${projectId}/resources`);
}

/** POST /api/v1/projects/{id}/resources */
export function createProjectResource(projectId: string | number, payload: Record<string, unknown>) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/projects/${projectId}/resources`, { method: 'POST', body: payload });
}

/** GET /api/v1/projects/{id}/status-reports */
export function getProjectStatusReports(projectId: string | number) {
  return apiFetch<ListEnvelope<Entity>>(`/api/v1/projects/${projectId}/status-reports`);
}

/** POST /api/v1/projects/{id}/status-reports */
export function createProjectStatusReport(projectId: string | number, payload: Record<string, unknown>) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/projects/${projectId}/status-reports`, { method: 'POST', body: payload });
}

/** GET /api/v1/projects/{id}/alerts — alertes calculées à la lecture. */
export function getProjectAlerts(projectId: string | number) {
  return apiFetch<ListEnvelope<Entity>>(`/api/v1/projects/${projectId}/alerts`);
}

// ---------------------------------------------------------------------------
// Registre des risques — probabilité × impact = score (1-9), criticité
// low/medium/high calculée côté backend (voir ProjectRisk::getCriticalityAttribute).
// ---------------------------------------------------------------------------

export const RISK_LEVEL_LABELS: Record<string, string> = { low: 'Faible', medium: 'Moyen', high: 'Élevé' };
export const RISK_CATEGORY_LABELS: Record<string, string> = {
  technical: 'Technique', financial: 'Financier', schedule: 'Planning',
  resource: 'Ressources', external: 'Externe', other: 'Autre',
};
export const RISK_STATUS_LABELS: Record<string, string> = {
  open: 'Ouvert', mitigated: 'Atténué', closed: 'Clôturé', occurred: 'Survenu',
};

export const risksApi = createResourceApi('project-risks');

/** GET /api/v1/projects/{id}/risks */
export function getProjectRisks(projectId: string | number) {
  return apiFetch<ListEnvelope<Entity>>(`/api/v1/projects/${projectId}/risks`);
}

/** POST /api/v1/projects/{id}/risks */
export function createProjectRisk(projectId: string | number, payload: Record<string, unknown>) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/projects/${projectId}/risks`, { method: 'POST', body: payload });
}

export function mitigateProjectRisk(riskId: string | number, mitigationPlan?: string) {
  return risksApi.action(riskId, 'mitigate', mitigationPlan ? { mitigation_plan: mitigationPlan } : {}) as Promise<ItemEnvelope<Entity>>;
}

export function closeProjectRisk(riskId: string | number) {
  return risksApi.action(riskId, 'close', {}) as Promise<ItemEnvelope<Entity>>;
}

export function markProjectRiskOccurred(riskId: string | number) {
  return risksApi.action(riskId, 'occur', {}) as Promise<ItemEnvelope<Entity>>;
}
