/**
 * Feature Workplaces — services connectés à l'API (D12 Collaboration).
 */
import { createResourceApi } from '@/lib/api/resources';

export const workplacesApi = createResourceApi('workplaces');
export const workplaceConversationsApi = createResourceApi('workplace-conversations');
export const workplaceTemplatesApi = createResourceApi('workplace-templates');
