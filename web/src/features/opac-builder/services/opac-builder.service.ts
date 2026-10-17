/**
 * Feature Constructeur OPAC — service connecté à l'API v1 du constructeur
 * (`/api/v1/opac/*`) et à l'API publique (`/api/public/opac/home`).
 */
import { apiFetch } from '@/lib/api/client';
import type { ItemEnvelope } from '@/lib/api/types';

// ============================ TYPES ============================

export type OpacPage = {
  id: number;
  slug: string;
  title: string;
  description: string | null;
  status: string;
  is_default: boolean;
  versions_count?: number;
  created_at?: string | null;
  updated_at?: string | null;
};

export type OpacBlock = {
  id?: number;
  zone: string;
  module_key: string;
  module?: Pick<OpacModule, 'key' | 'label' | 'icon' | 'variants' | 'settings'> | null;
  settings: Record<string, unknown>;
  responsive: Record<string, Record<string, unknown>> | null;
  order: number;
};

export type OpacPageVersion = {
  id: number;
  page_id: number;
  name: string;
  status: 'draft' | 'published' | 'archived';
  is_active: boolean;
  is_editable: boolean;
  change_description: string | null;
  published_at: string | null;
  created_by?: string | null;
  created_at?: string | null;
  updated_at?: string | null;
  blocks?: OpacBlock[];
};

export type OpacModuleSetting = {
  key: string;
  label: string;
  type: 'text' | 'textarea' | 'number' | 'select' | 'boolean' | 'url';
  default?: unknown;
  min?: number;
  max?: number;
  options?: { value: string; label: string }[];
  responsive?: boolean;
  help?: string;
};

export type OpacModule = {
  key: string;
  label: string;
  description: string | null;
  icon: string;
  zones: string[];
  variants: { key: string; label: string }[];
  settings: OpacModuleSetting[];
  source: {
    type?: string;
    endpoint?: string;
    params?: Record<string, string>;
    fixed?: Record<string, unknown>;
  } | null;
};

export type OpacCatalog = {
  zones: { key: string; label: string }[];
  breakpoints: { key: 'mobile' | 'tablet' | 'desktop'; label: string }[];
  modules: OpacModule[];
};

export type Breakpoint = 'mobile' | 'tablet' | 'desktop';

export type PageDetail = {
  page: OpacPage;
  versions: OpacPageVersion[];
};

// ============================ V1 (back-office) ============================

export async function getPages(): Promise<{ data: OpacPage[] }> {
  return apiFetch<{ data: OpacPage[] }>('/api/v1/opac/pages');
}

export async function getPage(id: number | string): Promise<ItemEnvelope<PageDetail>> {
  return apiFetch<ItemEnvelope<PageDetail>>(`/api/v1/opac/pages/${id}`);
}

export async function createPage(payload: Record<string, unknown>): Promise<ItemEnvelope<OpacPage>> {
  return apiFetch<ItemEnvelope<OpacPage>>('/api/v1/opac/pages', { method: 'POST', body: payload });
}

export async function updatePage(id: number | string, payload: Record<string, unknown>): Promise<ItemEnvelope<OpacPage>> {
  return apiFetch<ItemEnvelope<OpacPage>>(`/api/v1/opac/pages/${id}`, { method: 'PUT', body: payload });
}

export async function deletePage(id: number | string): Promise<unknown> {
  return apiFetch<unknown>(`/api/v1/opac/pages/${id}`, { method: 'DELETE' });
}

export async function getModuleCatalog(): Promise<ItemEnvelope<OpacCatalog>> {
  return apiFetch<ItemEnvelope<OpacCatalog>>('/api/v1/opac/modules');
}

export async function getPageVersions(pageId: number | string): Promise<{ data: OpacPageVersion[] }> {
  return apiFetch<{ data: OpacPageVersion[] }>(`/api/v1/opac/pages/${pageId}/versions`);
}

export async function createVersion(
  pageId: number | string,
  payload: { name: string; source_version_id?: number; change_description?: string },
): Promise<ItemEnvelope<OpacPageVersion>> {
  return apiFetch<ItemEnvelope<OpacPageVersion>>(`/api/v1/opac/pages/${pageId}/versions`, { method: 'POST', body: payload });
}

export async function getVersion(id: number | string): Promise<ItemEnvelope<OpacPageVersion>> {
  return apiFetch<ItemEnvelope<OpacPageVersion>>(`/api/v1/opac/versions/${id}`);
}

export async function updateVersion(
  id: number | string,
  payload: { name?: string; change_description?: string; blocks?: OpacBlock[] },
): Promise<ItemEnvelope<OpacPageVersion>> {
  return apiFetch<ItemEnvelope<OpacPageVersion>>(`/api/v1/opac/versions/${id}`, { method: 'PUT', body: payload });
}

export async function deleteVersion(id: number | string): Promise<unknown> {
  return apiFetch<unknown>(`/api/v1/opac/versions/${id}`, { method: 'DELETE' });
}

export async function activateVersion(id: number | string, change_description?: string): Promise<ItemEnvelope<OpacPageVersion>> {
  return apiFetch<ItemEnvelope<OpacPageVersion>>(`/api/v1/opac/versions/${id}/activate`, {
    method: 'POST',
    body: change_description ? { change_description } : undefined,
  });
}

export async function restoreVersion(id: number | string): Promise<ItemEnvelope<OpacPageVersion>> {
  return apiFetch<ItemEnvelope<OpacPageVersion>>(`/api/v1/opac/versions/${id}/restore`, { method: 'POST' });
}

export async function duplicateVersion(id: number | string): Promise<ItemEnvelope<OpacPageVersion>> {
  return apiFetch<ItemEnvelope<OpacPageVersion>>(`/api/v1/opac/versions/${id}/duplicate`, { method: 'POST' });
}

export async function diffVersions(
  id: number | string,
  withId: number | string,
): Promise<{ data: { added: OpacBlock[]; removed: OpacBlock[]; modified: { block: string }[] } }> {
  return apiFetch<{ data: { added: OpacBlock[]; removed: OpacBlock[]; modified: { block: string }[] } }>(
    `/api/v1/opac/versions/${id}/diff?with=${withId}`,
  );
}

// ============================ Public (rendu) ============================

export type OpacPublicHome = {
  page: OpacPage | null;
  version: OpacPageVersion | null;
  blocks: OpacBlock[];
};

export async function getPublicHome(): Promise<{ data: OpacPublicHome }> {
  return apiFetch<{ data: OpacPublicHome }>('/api/public/opac/home');
}
