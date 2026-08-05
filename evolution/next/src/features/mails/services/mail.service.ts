/**
 * Feature Mails — services connectés à l'API (D06 Courrier).
 */
import { createResourceApi } from '@/lib/api/resources';

export const mailsApi = createResourceApi('mails');
export const mailActionsApi = createResourceApi('mail-actions');
export const mailPrioritiesApi = createResourceApi('mail-priorities');
export const mailTypologiesApi = createResourceApi('mail-typologies');
export const batchesApi = createResourceApi('batches');
export const batchTransactionsApi = createResourceApi('batch-transactions');
export const mailContainersApi = createResourceApi('mail-containers');
export const mailArchivesApi = createResourceApi('mail-archives');
export const mailAttachmentsApi = createResourceApi('mail-attachments');
export { containerPropertiesApi } from '@/features/deposits/services/deposit.service';

