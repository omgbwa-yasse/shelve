/**
 * Feature OPAC — services connectés à l'API publique du portail (D15
 * `/api/public/*`). Types alignés sur les Ressources Laravel
 * (`Http/Resources/Api/Public/*`) et sur `PublicRecordApiController`.
 *
 * Deux enveloppes cohabitent :
 *  - records : `{ success, data, pagination, filters }` (contrôleur dédié) ;
 *  - news/events/pages : `{ data, meta, links }` (paginatedResponse).
 */
import { apiFetch } from '@/lib/api/client';
import { toQuery } from '@/lib/api/resources';
import type { ItemEnvelope, PaginatedEnvelope } from '@/lib/api/types';

// ======================== TYPES ========================

export type PublicPublisher = {
  id: number;
  name: string;
};

export type PublicThesaurusConcept = {
  id: number;
  term: string;
  weight?: number | null;
  context?: string | null;
};

/** Champs ISAD(G) essentiels — 3.1.1 à 3.3.1 (transformRecordForApi). */
export type PublicRecordIsad = {
  reference_code: string | null;
  title: string | null;
  dates: { start: string | null; end: string | null; exact: string | null; format: string | null };
  level_of_description: string | null;
  extent: string | null;
  creator: string | null;
  scope_content: string | null;
};

/** Notice exposée par `/api/public/records`. */
export type PublicRecord = {
  id: number;
  title: string;
  code: string;
  content: string;
  description: string;
  subtitle: string;
  reference: string;
  type: string;
  date: string | null;
  date_range: string | null;
  formatted_date_range: string | null;
  published_at: string | null;
  expires_at: string | null;
  publication_notes: string | null;
  is_available: boolean;
  is_expired: boolean;
  digital_copy_available: boolean;
  created_at: string | null;
  updated_at: string | null;
  thumbnail_url: string | null;
  images: unknown[];
  attachments: unknown[];
  tags: unknown[];
  publisher: PublicPublisher | null;
  isad_fields: PublicRecordIsad;
  classifications: unknown[];
  thesaurus_concepts: PublicThesaurusConcept[];
  // Champs détaillés (show) — ajoutés conditionnellement par le backend.
  access_conditions?: string | null;
  classification?: string | null;
  series?: string | null;
  location?: string | null;
  format?: string | null;
  dimensions?: string | null;
  language?: string | null;
  biographical_history?: string | null;
  archival_history?: string | null;
  reproduction_conditions?: string | null;
  characteristic?: string | null;
  finding_aids?: string | null;
  related_unit?: string | null;
  publication_note?: string | null;
  note?: string | null;
};

export type PublicAuthorRef = { id: number; name: string } | null;

export type PublicNews = {
  id: number;
  name: string;
  title: string;
  slug: string;
  summary: string;
  content: string;
  featured: boolean;
  published_at: string | null;
  image_path: string | null;
  image_url: string | null;
  author: PublicAuthorRef;
  created_at: string | null;
  updated_at: string | null;
};

export type PublicEvent = {
  id: number;
  name: string;
  description: string;
  start_date: string | null;
  end_date: string | null;
  location: string | null;
  is_online: boolean;
  online_link: string | null;
  registration_count?: number | null;
  created_at: string | null;
  updated_at: string | null;
};

export type PublicPage = {
  id: number;
  title: string;
  name: string;
  slug: string;
  content: string;
  meta_description: string | null;
  meta_keywords: string | null;
  featured_image_path: string | null;
  order: number | null;
  parent_id: number | null;
  is_published: boolean;
  author: PublicAuthorRef;
  created_at: string | null;
  updated_at: string | null;
};

/** Enveloppe du contrôleur records — pagination sous la clé `pagination`. */
export type PublicRecordsList = {
  success: boolean;
  data: PublicRecord[];
  pagination: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
  };
  filters?: Record<string, unknown>;
};

export type PublicRecordsQuery = {
  search?: string;
  type?: string;
  date_from?: string;
  date_to?: string;
  language?: string;
  publisher_id?: number;
  level_id?: number;
  status_id?: number;
  support_id?: number;
  activity_id?: number;
  thesaurus_concept_id?: number;
  date_format?: string;
  has_digital_copy?: boolean;
  per_page?: number;
  page?: number;
};

// ======================== RECORDS ========================

/** Liste/recherche paginée — enveloppe records (`pagination`). */
export async function listPublicRecords(params: PublicRecordsQuery = {}): Promise<PublicRecordsList> {
  return apiFetch<PublicRecordsList>(`/api/public/records${toQuery(params as Record<string, unknown>)}`);
}

export async function getPublicRecord(id: number | string): Promise<ItemEnvelope<PublicRecord>> {
  return apiFetch<ItemEnvelope<PublicRecord>>(`/api/public/records/${id}`);
}

// ======================== NEWS ========================

export type PublicNewsQuery = {
  search?: string;
  featured?: boolean;
  date_from?: string;
  date_to?: string;
  sort_by?: string;
  sort_order?: 'asc' | 'desc';
  per_page?: number;
  page?: number;
};

export async function listPublicNews(params: PublicNewsQuery = {}): Promise<PaginatedEnvelope<PublicNews>> {
  return apiFetch<PaginatedEnvelope<PublicNews>>(`/api/public/news${toQuery(params as Record<string, unknown>)}`);
}

export async function getPublicNews(id: number | string): Promise<ItemEnvelope<PublicNews>> {
  return apiFetch<ItemEnvelope<PublicNews>>(`/api/public/news/${id}`);
}

export async function getLatestNews(limit = 5): Promise<{ data: PublicNews[] }> {
  return apiFetch<{ data: PublicNews[] }>(`/api/public/news/latest${toQuery({ limit })}`);
}

// ======================== EVENTS ========================

export type PublicEventFilter = 'upcoming' | 'past' | 'today' | 'this_week' | 'this_month';

export type PublicEventsQuery = {
  search?: string;
  filter?: PublicEventFilter;
  date_from?: string;
  date_to?: string;
  is_online?: boolean;
  sort?: string;
  per_page?: number;
  page?: number;
};

export async function listPublicEvents(params: PublicEventsQuery = {}): Promise<PaginatedEnvelope<PublicEvent>> {
  return apiFetch<PaginatedEnvelope<PublicEvent>>(`/api/public/events${toQuery(params as Record<string, unknown>)}`);
}

export async function getPublicEvent(id: number | string): Promise<ItemEnvelope<PublicEvent>> {
  return apiFetch<ItemEnvelope<PublicEvent>>(`/api/public/events/${id}`);
}

// ======================== PAGES ========================

export type PublicPagesQuery = {
  search?: string;
  per_page?: number;
  page?: number;
};

export async function listPublicPages(params: PublicPagesQuery = {}): Promise<PaginatedEnvelope<PublicPage>> {
  return apiFetch<PaginatedEnvelope<PublicPage>>(`/api/public/pages${toQuery(params as Record<string, unknown>)}`);
}

export async function getPublicPage(id: number | string): Promise<ItemEnvelope<PublicPage>> {
  return apiFetch<ItemEnvelope<PublicPage>>(`/api/public/pages/${id}`);
}
