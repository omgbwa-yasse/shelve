/**
 * Feature Public — services connectés à l'API publique (D15 /api/public).
 */
import { createPublicResourceApi } from '@/lib/api/resources';

export const publicRecordsApi = createPublicResourceApi('records');
export const publicNewsApi = createPublicResourceApi('news');
export const publicEventsApi = createPublicResourceApi('events');
export const publicPagesApi = createPublicResourceApi('pages');
export const publicTemplatesApi = createPublicResourceApi('templates');
export const publicUsersApi = createPublicResourceApi('users');
export const publicFeedbacksApi = createPublicResourceApi('feedbacks');
export const publicSearchLogsApi = createPublicResourceApi('search-logs');
