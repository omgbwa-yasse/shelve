/**
 * Feature Email — boîte de messagerie IMAP/SMTP (distincte du courrier
 * administratif de `mail.service.ts`). Consomme `routes/api/Email.php`.
 */
import { apiFetch } from '@/lib/api/client';
import { createResourceApi } from '@/lib/api/resources';
import type { Entity, ItemEnvelope, ListEnvelope, PaginatedEnvelope } from '@/lib/api/types';

export const emailAccountsApi = createResourceApi('email-accounts');
export const emailMessagesApi = createResourceApi('email-messages');
export const emailTagsApi = createResourceApi('email-tags');

export const EMAIL_ENCRYPTION_LABELS: Record<string, string> = {
  ssl: 'SSL',
  tls: 'TLS',
  none: 'Aucun',
};

/** GET /api/v1/email-messages?filter[...]&include=tags,attachments */
export function getEmailMessages(params: Record<string, unknown> = {}) {
  return apiFetch<PaginatedEnvelope<Entity>>(
    `/api/v1/email-messages?${new URLSearchParams({ include: 'tags,attachments', ...params } as never).toString()}`
  );
}

/** GET /api/v1/email-messages/{id} — marque le message comme lu côté serveur. */
export function getEmailMessage(id: string | number) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/email-messages/${id}`);
}

/** GET /api/v1/email-tags */
export function getEmailTags() {
  return apiFetch<ListEnvelope<Entity>>('/api/v1/email-tags');
}

/** POST /api/v1/email-accounts/{id}/sync — synchro IMAP immédiate en arrière-plan. */
export function syncEmailAccount(id: string | number) {
  return apiFetch<{ message: string }>(`/api/v1/email-accounts/${id}/sync`, { method: 'POST' });
}

/** POST /api/v1/email-accounts/{id}/toggle-active — active/désactive un compte utilisateur. */
export function toggleEmailAccountActive(id: string | number) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/email-accounts/${id}/toggle-active`, { method: 'POST' });
}

/** GET /api/v1/email — statut du module pour l'organisation courante. */
export function getEmailModuleStatus() {
  return apiFetch<{ data: { enabled: boolean } }>('/api/v1/email');
}

/** POST /api/v1/email/toggle — active/désactive le module (admin). */
export function toggleEmailModule() {
  return apiFetch<{ data: { enabled: boolean } }>('/api/v1/email/toggle', { method: 'POST' });
}

/** POST /api/v1/email-messages/send — compose + envoi réel via SMTP. */
export function sendEmailMessage(payload: {
  email_account_id: string | number;
  to: string[];
  cc?: string[];
  bcc?: string[];
  subject: string;
  body_html: string;
  in_reply_to?: string | null;
}) {
  return apiFetch<ItemEnvelope<Entity>>('/api/v1/email-messages/send', { method: 'POST', body: payload });
}

export function attachEmailTag(messageId: string | number, tagId: string | number) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/email-messages/${messageId}/tags`, {
    method: 'POST',
    body: { tag_id: tagId },
  });
}

export function detachEmailTag(messageId: string | number, tagId: string | number) {
  return apiFetch<unknown>(`/api/v1/email-messages/${messageId}/tags/${tagId}`, { method: 'DELETE' });
}

export function toggleEmailFlag(message: Entity) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/email-messages/${message.id}`, {
    method: 'PUT',
    body: { is_flagged: !message.is_flagged },
  });
}
