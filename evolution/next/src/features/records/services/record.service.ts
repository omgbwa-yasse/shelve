/**
 * Feature Records (Notices) — services connectés à l'API (D02 + phase 9).
 */
import { createResourceApi } from '@/lib/api/resources';

export const recordsApi = createResourceApi('records');
export const recordStatusesApi = createResourceApi('record-statuses');
export const recordSupportsApi = createResourceApi('record-supports');
export const recordTypesApi = createResourceApi('record-types');
export const metadataDefinitionsApi = createResourceApi('metadata-definitions');
export const recordReactivationsApi = createResourceApi('record-reactivations');
export const digitalFoldersApi = createResourceApi('digital-folders');
export const digitalDocumentsApi = createResourceApi('digital-documents');
export const authorsApi = createResourceApi('authors');
export const authorContactsApi = createResourceApi('author-contacts');
