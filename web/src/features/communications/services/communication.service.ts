/**
 * Feature Communications — services connectés à l'API (D05).
 */
import { createResourceApi } from '@/lib/api/resources';

export const communicationsApi = createResourceApi('communications');
export const reservationsApi = createResourceApi('reservations');
