/**
 * Feature Transferts — services connectés à l'API (D04 + D07).
 */
import { createResourceApi } from '@/lib/api/resources';

export const slipsApi = createResourceApi('slips');
export const slipStatusesApi = createResourceApi('slip-statuses');
export const declassementListsApi = createResourceApi('declassement-lists');
export const retentionsApi = createResourceApi('retentions');
