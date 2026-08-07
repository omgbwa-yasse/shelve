/**
 * Feature Outils — services connectés à l'API (D01 référentiels + D08 thésaurus).
 */
import { apiFetch } from '@/lib/api/client';
import { createResourceApi } from '@/lib/api/resources';
import type { Entity, ItemEnvelope } from '@/lib/api/types';

export const activitiesApi = createResourceApi('activities');
export const communicabilitiesApi = createResourceApi('communicabilities');
export const keywordsApi = createResourceApi('keywords');
export const languagesApi = createResourceApi('languages');
export const sortsApi = createResourceApi('sorts');
export const lawsApi = createResourceApi('laws');
export const lawArticlesApi = createResourceApi('law-articles');
export const referenceListsApi = createResourceApi('reference-lists');
export const organisationsApi = createResourceApi('organisations');
export const thesaurusSchemesApi = createResourceApi('thesaurus-schemes');
export const thesaurusConceptsApi = createResourceApi('thesaurus-concepts');

// --- Domaines de valeurs : valeurs imbriquées --------------------------------

/** GET /api/v1/reference-lists/{id} — liste avec ses valeurs chargées. */
export function getReferenceList(id: string | number): Promise<ItemEnvelope<Entity>> {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/reference-lists/${id}`);
}

/** POST /api/v1/reference-lists/{id}/values */
export function addReferenceValue(
  listId: string | number,
  payload: Record<string, unknown>,
): Promise<ItemEnvelope<Entity>> {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/reference-lists/${listId}/values`, {
    method: 'POST',
    body: payload,
  });
}

/** PATCH /api/v1/reference-lists/{id}/values/{valueId} */
export function updateReferenceValue(
  listId: string | number,
  valueId: string | number,
  payload: Record<string, unknown>,
): Promise<ItemEnvelope<Entity>> {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/reference-lists/${listId}/values/${valueId}`, {
    method: 'PATCH',
    body: payload,
  });
}

/** DELETE /api/v1/reference-lists/{id}/values/{valueId} */
export function deleteReferenceValue(
  listId: string | number,
  valueId: string | number,
): Promise<unknown> {
  return apiFetch<unknown>(`/api/v1/reference-lists/${listId}/values/${valueId}`, {
    method: 'DELETE',
  });
}
