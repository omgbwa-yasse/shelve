/**
 * Feature Dépôts — services connectés à l'API (D03 localisation physique).
 */
import { createResourceApi } from '@/lib/api/resources';

export const buildingsApi = createResourceApi('buildings');
export const floorsApi = createResourceApi('floors');
export const roomsApi = createResourceApi('rooms');
export const shelvesApi = createResourceApi('shelves');
export const containersApi = createResourceApi('containers');
export const containerPropertiesApi = createResourceApi('container-properties');
export const containerStatusesApi = createResourceApi('container-statuses');
