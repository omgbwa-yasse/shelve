/**
 * Feature Workplaces — services connectés à l'API (D12 Collaboration).
 *
 * Le workplace est désormais adressé par son **code** (slug) : `/workplace/{code}`
 * (ex. `rh`, `sia2019`, `dg-sg`). Le backend résout aussi bien par code que par id,
 * ce qui conserve la compatibilité des anciens liens `/workplaces/{id}`.
 */
import { apiFetch } from '@/lib/api/client';
import { createResourceApi } from '@/lib/api/resources';
import type { ItemEnvelope, ListEnvelope, PaginatedEnvelope } from '@/lib/api/types';
import type {
  Workplace,
  WorkplaceActivity,
  WorkplaceConversation,
  WorkplaceDocument,
  WorkplaceMember,
  WorkplaceMessage,
} from '../types';

export const workplacesApi = createResourceApi<Workplace>('workplaces');
export const workplaceConversationsApi = createResourceApi<WorkplaceConversation>(
  'workplace-conversations',
);
export const workplaceTemplatesApi = createResourceApi('workplace-templates');

/** Charge un workplace (par code ou id) avec les relations du tableau de bord. */
export function getWorkplace(codeOrId: string): Promise<ItemEnvelope<Workplace>> {
  return apiFetch<ItemEnvelope<Workplace>>(
    `/api/v1/workplaces/${encodeURIComponent(codeOrId)}?include=category,owner,members.user,activities`,
  );
}

/** Membres d'un espace (utilise le code). */
export function listMembers(code: string): Promise<ListEnvelope<WorkplaceMember>> {
  return apiFetch<ListEnvelope<WorkplaceMember>>(`/api/v1/workplaces/${encodeURIComponent(code)}/members`);
}

export function inviteMember(
  code: string,
  payload: { email: string; role: string; message?: string },
): Promise<ItemEnvelope<WorkplaceMember>> {
  return apiFetch<ItemEnvelope<WorkplaceMember>>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/members`,
    { method: 'POST', body: payload },
  );
}

export function updateMemberRole(
  code: string,
  memberId: number,
  role: string,
): Promise<ItemEnvelope<WorkplaceMember>> {
  return apiFetch<ItemEnvelope<WorkplaceMember>>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/members/${memberId}`,
    { method: 'PUT', body: { role } },
  );
}

export function removeMember(code: string, memberId: number): Promise<unknown> {
  return apiFetch<unknown>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/members/${memberId}`,
    { method: 'DELETE' },
  );
}

export type ActivityListParams = {
  'filter[activity_type]'?: string;
  'filter[user_id]'?: string;
  'filter[created_at][gte]'?: string;
  'filter[created_at][lte]'?: string;
  page?: number;
  'page.size'?: number;
  sort?: string;
};

/** Activités d'un espace (lecture seule), filtrables. */
export function listActivities(
  code: string,
  params: ActivityListParams = {},
): Promise<PaginatedEnvelope<WorkplaceActivity>> {
  const query = new URLSearchParams(
    Object.entries(params).filter(([, v]) => v !== undefined && v !== '') as [string, string][],
  ).toString();
  return apiFetch<PaginatedEnvelope<WorkplaceActivity>>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/activities${query ? `?${query}` : ''}`,
  );
}

export function archiveWorkplace(code: string): Promise<ItemEnvelope<Workplace>> {
  return apiFetch<ItemEnvelope<Workplace>>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/archive`,
    { method: 'POST' },
  );
}

/** Événement daté du calendrier d'un workplace (projets/jalons/tâches). */
export type WorkplaceCalendarEvent = {
  date: string;
  type: 'project_start' | 'project_end' | 'milestone' | 'task_due';
  title: string;
  subtitle?: string | null;
  project_id?: number | null;
  color?: string | null;
};

export function getWorkplaceCalendar(
  code: string,
): Promise<{ data: WorkplaceCalendarEvent[] }> {
  return apiFetch<{ data: WorkplaceCalendarEvent[] }>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/calendar`,
  );
}

/** Contenu (dossiers + fichiers) d'un répertoire de la bibliothèque Documents. */
export function listWorkplaceDocuments(
  code: string,
  parentId?: string | number | null,
): Promise<{ data: WorkplaceDocument[] }> {
  const query = parentId != null ? `?parent_id=${parentId}` : '';
  return apiFetch<{ data: WorkplaceDocument[] }>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/documents${query}`,
  );
}

export function createWorkplaceFolder(
  code: string,
  payload: { name: string; parent_id?: number | null },
): Promise<{ data: WorkplaceDocument }> {
  return apiFetch<{ data: WorkplaceDocument }>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/folders`,
    { method: 'POST', body: payload },
  );
}

/** Upload multipart — ne passe pas par `apiFetch` (Content-Type json). */
export async function uploadWorkplaceDocument(
  code: string,
  file: File,
  payload: { name?: string; parent_id?: number | null },
): Promise<{ data: WorkplaceDocument }> {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('name', payload.name ?? file.name);
  if (payload.parent_id != null) formData.append('parent_id', String(payload.parent_id));

  const response = await fetch(
    `/api/proxy/api/v1/workplaces/${encodeURIComponent(code)}/documents/upload`,
    {
      method: 'POST',
      credentials: 'same-origin',
      headers: { Accept: 'application/json' },
      body: formData,
    },
  );

  if (!response.ok) {
    const body = await response.json().catch(() => null);
    throw new Error((body as { message?: string })?.message ?? `Échec de l'envoi (${response.status})`);
  }

  return response.json() as Promise<{ data: WorkplaceDocument }>;
}

export function deleteWorkplaceDocument(
  code: string,
  documentId: string | number,
): Promise<unknown> {
  return apiFetch<unknown>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/documents/${documentId}`,
    { method: 'DELETE' },
  );
}

/** Partage un document du workplace vers le module Records. */
export function shareWorkplaceDocument(
  code: string,
  documentId: string | number,
): Promise<{ data: WorkplaceDocument }> {
  return apiFetch<{ data: WorkplaceDocument }>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/documents/${documentId}/share`,
    { method: 'POST' },
  );
}

/** Retire le partage (le document redevient invisible du module Records). */
export function unshareWorkplaceDocument(
  code: string,
  documentId: string | number,
): Promise<{ data: WorkplaceDocument }> {
  return apiFetch<{ data: WorkplaceDocument }>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/documents/${documentId}/unshare`,
    { method: 'POST' },
  );
}

/** Transfère le document vers le module Records, affecté à une classe du plan. */
export function transferWorkplaceDocument(
  code: string,
  documentId: string | number,
  activityId: string | number,
): Promise<{ data: { id: number; transferred: boolean } }> {
  return apiFetch<{ data: { id: number; transferred: boolean } }>(
    `/api/v1/workplaces/${encodeURIComponent(code)}/documents/${documentId}/transfer`,
    { method: 'POST', body: { activity_id: activityId } },
  );
}

/** URL de téléchargement — à utiliser via `window.location` (réponse binaire). */
export function downloadWorkplaceDocumentUrl(code: string, documentId: string | number): string {
  return `/api/proxy/api/v1/workplaces/${encodeURIComponent(code)}/documents/${documentId}/download`;
}

/** Plan de classement (activités) pour le transfert vers Records. */
export function listClassificationActivities(): Promise<PaginatedEnvelope<{ id: number; code?: string; name: string; parent_id?: number | null }>> {
  return apiFetch<PaginatedEnvelope<{ id: number; code?: string; name: string; parent_id?: number | null }>>(
    '/api/v1/activities?page.size=100&sort=name',
  );
}

export function destroyWorkplace(code: string): Promise<unknown> {
  return apiFetch<unknown>(`/api/v1/workplaces/${encodeURIComponent(code)}`, { method: 'DELETE' });
}

/** Conversations d'un espace (l'utilisateur doit être participant). */
export function listConversations(
  workplaceId: number | string,
): Promise<PaginatedEnvelope<WorkplaceConversation>> {
  return apiFetch<PaginatedEnvelope<WorkplaceConversation>>(
    `/api/v1/workplace-conversations?filter[workplace_id]=${workplaceId}&include=participants.user,messages.user&sort=-updated_at&page.size=100`,
  );
}

export function getConversation(id: number | string): Promise<ItemEnvelope<WorkplaceConversation>> {
  return apiFetch<ItemEnvelope<WorkplaceConversation>>(
    `/api/v1/workplace-conversations/${id}?include=creator,participants.user,messages.user,workplace`,
  );
}

export function createConversation(payload: {
  workplace_id: number;
  type: 'group' | 'channel' | 'private';
  name?: string;
  participant_ids: number[];
}): Promise<ItemEnvelope<WorkplaceConversation>> {
  return apiFetch<ItemEnvelope<WorkplaceConversation>>('/api/v1/workplace-conversations', {
    method: 'POST',
    body: payload,
  });
}

export function destroyConversation(id: number | string): Promise<unknown> {
  return apiFetch<unknown>(`/api/v1/workplace-conversations/${id}`, { method: 'DELETE' });
}

export function sendMessage(
  conversationId: number | string,
  content: string,
): Promise<ItemEnvelope<WorkplaceMessage>> {
  return apiFetch<ItemEnvelope<WorkplaceMessage>>(
    `/api/v1/workplace-conversations/${conversationId}/messages`,
    { method: 'POST', body: { content } },
  );
}
