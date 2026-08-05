/**
 * Feature Contacts — services connectés à l'API (D01 sous-lot contacts).
 */
import { createResourceApi } from '@/lib/api/resources';

export const externalContactsApi = createResourceApi('external-contacts');
export const externalOrganizationsApi = createResourceApi('external-organizations');
export const authorsApi = createResourceApi('authors');
export const authorContactsApi = createResourceApi('author-contacts');
