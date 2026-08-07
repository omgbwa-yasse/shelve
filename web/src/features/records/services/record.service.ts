/**
 * Feature Records (Notices) — services connectés à l'API (D02 + phase 9).
 */
import { apiFetch } from '@/lib/api/client';
import { createResourceApi } from '@/lib/api/resources';
import type { Entity, ItemEnvelope, ListEnvelope, PaginatedEnvelope } from '@/lib/api/types';

export const recordsApi = createResourceApi('records');
export const recordStatusesApi = createResourceApi('record-statuses');
export const recordSupportsApi = createResourceApi('record-supports');
export const recordTypesApi = createResourceApi('record-types');
export const metadataDefinitionsApi = createResourceApi('metadata-definitions');
export const recordReactivationsApi = createResourceApi('record-reactivations');
export const authorsApi = createResourceApi('authors');
export const authorContactsApi = createResourceApi('author-contacts');

/** Champs "notice" — mêmes libellés que le formulaire (voir `StoreRecordRequest`). */
export const ACCESS_LEVEL_LABELS: Record<string, string> = {
  internal: 'Interne',
  public: 'Public',
  restricted: 'Restreint',
};

export const DATE_FORMAT_LABELS: Record<string, string> = {
  Y: 'Année',
  M: 'Mois',
  D: 'Jour',
};

// --- Notice : recherche par libellé, corbeille, métadonnées ------------------

/** GET /api/v1/records?include=type,level,status,activity,... */
export function getRecords(params: Record<string, unknown> = {}) {
  const include = 'type,level,status,activity,parent';
  return apiFetch<PaginatedEnvelope<Entity>>(`/api/v1/records?${new URLSearchParams({ include, ...params } as never).toString()}`);
}

/** GET /api/v1/records/{id}?include=... — fiche complète avec libellés résolus. */
export function getRecord(id: string | number) {
  const include = 'type,level,status,activity,parent,organisation,creator,assignedUser,approver,confidentiality,accessLimit,mediums,authors,keywords,attachments';
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/records/${id}?include=${include}`);
}

/** GET /api/v1/records-trash — notices supprimées (soft delete), jamais purgées. */
export function getRecordsTrash(params: Record<string, unknown> = {}) {
  return apiFetch<PaginatedEnvelope<Entity>>(`/api/v1/records-trash?${new URLSearchParams({ include: 'type', ...params } as never).toString()}`);
}

/** POST /api/v1/records/{id}/restore */
export function restoreRecord(id: string | number) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/records/${id}/restore`, { method: 'POST' });
}

/** DELETE /api/v1/records/{id}/force — suppression définitive, hors de la corbeille. */
export function forceDeleteRecord(id: string | number) {
  return apiFetch<unknown>(`/api/v1/records/${id}/force`, { method: 'DELETE' });
}

/** GET /api/v1/records/{id}/metadata-fields — schéma + valeurs actuelles. */
export function getRecordMetadataFields(id: string | number) {
  return apiFetch<ListEnvelope<Entity>>(`/api/v1/records/${id}/metadata-fields`);
}

/** GET /api/v1/record-types/{id}/metadata-fields — schéma seul (avant création). */
export function getRecordTypeMetadataFields(typeId: string | number) {
  return apiFetch<ListEnvelope<Entity>>(`/api/v1/record-types/${typeId}/metadata-fields`);
}

/** GET /api/v1/record-levels */
export function getRecordLevels() {
  return apiFetch<ListEnvelope<Entity>>('/api/v1/record-levels');
}

/** GET /api/v1/record-confidentialities */
export function getRecordConfidentialities() {
  return apiFetch<ListEnvelope<Entity>>('/api/v1/record-confidentialities');
}

// --- Sous-notices (enfants) ---------------------------------------------------

export function getRecordChildren(recordId: string | number) {
  return apiFetch<PaginatedEnvelope<Entity>>(`/api/v1/records/${recordId}/children?include=type,level,status&page.size=50`);
}

export function createRecordChild(recordId: string | number, payload: Record<string, unknown>) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/records/${recordId}/children`, { method: 'POST', body: payload });
}

export function deleteRecordChild(recordId: string | number, childId: string | number) {
  return apiFetch<unknown>(`/api/v1/records/${recordId}/children/${childId}`, { method: 'DELETE' });
}

// --- Contenants ----------------------------------------------------------------

export function getRecordContainers(recordId: string | number) {
  return apiFetch<PaginatedEnvelope<Entity>>(`/api/v1/records/${recordId}/containers?include=container&page.size=50`);
}

export function attachRecordContainer(recordId: string | number, containerId: string | number, description?: string) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/records/${recordId}/containers`, {
    method: 'POST',
    body: { container_id: containerId, description },
  });
}

export function detachRecordContainer(recordId: string | number, containerId: string | number) {
  return apiFetch<unknown>(`/api/v1/records/${recordId}/containers/${containerId}`, { method: 'DELETE' });
}

// --- Auteurs ---------------------------------------------------------------------

export function getRecordAuthors(recordId: string | number) {
  return apiFetch<PaginatedEnvelope<Entity>>(`/api/v1/records/${recordId}/authors?page.size=50`);
}

export function attachRecordAuthor(recordId: string | number, authorId: string | number) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/records/${recordId}/authors`, { method: 'POST', body: { author_id: authorId } });
}

export function detachRecordAuthor(recordId: string | number, authorId: string | number) {
  return apiFetch<unknown>(`/api/v1/records/${recordId}/authors/${authorId}`, { method: 'DELETE' });
}

// --- Pièces jointes ---------------------------------------------------------------

export function getRecordAttachments(recordId: string | number) {
  return apiFetch<PaginatedEnvelope<Entity>>(`/api/v1/records/${recordId}/attachments?include=attachment&page.size=50`);
}

/**
 * POST /api/v1/records/{id}/attachments/upload — multipart, ne passe pas par
 * `apiFetch` (qui force `Content-Type: application/json` et `JSON.stringify` du
 * corps) : le navigateur doit poser lui-même la frontière `multipart/form-data`.
 */
export async function uploadRecordAttachment(recordId: string | number, file: File): Promise<ItemEnvelope<Entity>> {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('name', file.name);

  const response = await fetch(`/api/proxy/api/v1/records/${recordId}/attachments/upload`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
    body: formData,
  });

  if (!response.ok) {
    throw new Error(`Échec de l'envoi (${response.status})`);
  }

  return response.json() as Promise<ItemEnvelope<Entity>>;
}

export function deleteRecordAttachment(recordId: string | number, attachmentId: string | number) {
  return apiFetch<unknown>(`/api/v1/records/${recordId}/attachments/${attachmentId}`, { method: 'DELETE' });
}
