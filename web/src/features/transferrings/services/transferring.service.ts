/**
 * Feature Transferts - services connectés à l'API (D04 + D07).
 */
import { apiFetch } from '@/lib/api/client';
import { createResourceApi } from '@/lib/api/resources';

export const slipsApi = createResourceApi('slips');
export const slipStatusesApi = createResourceApi('slip-statuses');
export const declassementListsApi = createResourceApi('declassement-lists');
export const retentionsApi = createResourceApi('retentions');

export type LifecycleStage = 'avant_dua' | 'apres_dua' | 'apres_dul';

export type LifecycleRecord = {
  id: number;
  code?: string;
  name?: string;
  activity_id?: number | null;
  status_id?: number | null;
  level_id?: number | null;
  organisation_id?: number;
  start_date?: string | null;
  end_date?: string | null;
  date_exact?: string | null;
  reference_date?: string | null;
  dua_years?: number;
  dul_years?: number;
  dua_end_date?: string | null;
  dul_end_date?: string | null;
  stage?: LifecycleStage;
  sort?: string | null;
  sort_name?: string | null;
  retention?: { id: number; code?: string; name?: string; duration?: number; sort_id?: number } | null;
  law?: {
    law_article_id?: number;
    law_article_code?: string;
    law_article_name?: string;
    law_id?: number;
    law_code?: string;
    law_name?: string;
    law_type_id?: number;
  } | null;
};

/** GET /api/v1/declassement-lists/lifecycle?stage=... — cycle de vie DUA/DUL. */
export function getDeclassementLifecycle(stage?: LifecycleStage) {
  return apiFetch<{ data: LifecycleRecord[] }>(
    `/api/v1/declassement-lists/lifecycle${stage ? `?stage=${stage}` : ''}`,
  );
}

