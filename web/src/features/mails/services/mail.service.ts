/**
 * Feature Mails — services connectés à l'API (D06 Courrier).
 */
import { createResourceApi } from '@/lib/api/resources';
import { apiFetch } from '@/lib/api/client';

export const mailsApi = createResourceApi('mails');
export const mailActionsApi = createResourceApi('mail-actions');
export const mailPrioritiesApi = createResourceApi('mail-priorities');
export const mailTypologiesApi = createResourceApi('mail-typologies');
export const batchesApi = createResourceApi('batches');
export const batchTransactionsApi = createResourceApi('batch-transactions');
export const mailArchivesApi = createResourceApi('mail-archives');
export const mailAttachmentsApi = createResourceApi('mail-attachments');
export { containerPropertiesApi, roomsApi, shelvesApi, containersApi } from '@/features/deposits/services/deposit.service';

/** GET /api/v1/mails/external-capabilities — capacités courrier externe de l'organisation courante. */
export function getMailExternalCapabilities() {
  return apiFetch<{ data: { external_send_enabled: boolean; external_receive_enabled: boolean } }>(
    '/api/v1/mails/external-capabilities',
  );
}

