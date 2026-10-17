/**
 * Feature Chat — services connectés à l'API (conversations D12).
 *
 * Le chat global (`/chats`) liste TOUTES les conversations de l'utilisateur
 * (privées et de workplace) : le backend filtre par participant. La création
 * d'un message privé réutilise une conversation existante entre les deux
 * membres quand elle existe déjà (comportement `WorkplaceConversationController`).
 */
import { apiFetch } from '@/lib/api/client';
import type { ItemEnvelope, PaginatedEnvelope } from '@/lib/api/types';
import type { WorkplaceConversation, WorkplaceMessage } from '@/features/workplaces/types';

const BASE = '/api/v1/workplace-conversations';

/** GET /api/v1/workplace-conversations?include=...&sort=-updated_at — conversations de l'utilisateur. */
export function listChats(): Promise<PaginatedEnvelope<WorkplaceConversation>> {
  return apiFetch<PaginatedEnvelope<WorkplaceConversation>>(
    `${BASE}?include=participants.user,messages.user,workplace&sort=-updated_at&page.size=100`,
  );
}

/** GET /api/v1/workplace-conversations/{id}?include=... — fil complet. */
export function getChat(id: number | string): Promise<ItemEnvelope<WorkplaceConversation>> {
  return apiFetch<ItemEnvelope<WorkplaceConversation>>(
    `${BASE}/${id}?include=creator,participants.user,messages.user,workplace`,
  );
}

/** POST /api/v1/workplace-conversations/{id}/messages */
export function sendChatMessage(
  conversationId: number | string,
  content: string,
): Promise<ItemEnvelope<WorkplaceMessage>> {
  return apiFetch<ItemEnvelope<WorkplaceMessage>>(`${BASE}/${conversationId}/messages`, {
    method: 'POST',
    body: { content },
  });
}

/** POST /api/v1/workplace-conversations — message privé avec un collègue. */
export function createPrivateChat(
  colleagueId: number,
): Promise<ItemEnvelope<WorkplaceConversation>> {
  return apiFetch<ItemEnvelope<WorkplaceConversation>>(BASE, {
    method: 'POST',
    body: { type: 'private', participant_ids: [colleagueId] },
  });
}

/** POST /api/v1/workplace-conversations — groupe créé à partir de plusieurs contacts. */
export function createGroupChat(
  participantIds: number[],
  name: string,
): Promise<ItemEnvelope<WorkplaceConversation>> {
  return apiFetch<ItemEnvelope<WorkplaceConversation>>(BASE, {
    method: 'POST',
    body: { type: 'group', name, participant_ids: participantIds },
  });
}

/**
 * POST /api/v1/workplace-conversations/{id}/messages/attachment — multipart,
 * ne passe pas par `apiFetch` (Content-Type json) : le navigateur pose la
 * frontière multipart/form-data lui-même.
 */
export async function sendChatAttachment(
  conversationId: number | string,
  file: File,
  content = '',
): Promise<ItemEnvelope<WorkplaceMessage>> {
  const formData = new FormData();
  formData.append('file', file);
  if (content) formData.append('content', content);

  const response = await fetch(`/api/proxy${BASE}/${conversationId}/messages/attachment`, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
    body: formData,
  });

  if (!response.ok) {
    const body = await response.json().catch(() => null);
    throw new Error((body as { message?: string })?.message ?? `Échec de l'envoi (${response.status})`);
  }

  return response.json() as Promise<ItemEnvelope<WorkplaceMessage>>;
}

/** URL de téléchargement d'une pièce jointe de message. */
export function chatAttachmentUrl(
  conversationId: number | string,
  messageId: number | string,
): string {
  return `/api/proxy${BASE}/${conversationId}/messages/${messageId}/attachment`;
}
