import { apiFetch } from '@/lib/api/client';
import type { Entity, ListParams, ItemEnvelope, PaginatedEnvelope } from '@/lib/api/types';

export function toQuery(params?: Record<string, unknown>): string {
  if (!params) return '';
  const entries = Object.entries(params).filter(
    ([, v]) => v !== undefined && v !== null && v !== '',
  );
  if (entries.length === 0) return '';
  return `?${new URLSearchParams(entries as [string, string][]).toString()}`;
}

export type ResourceApi<T extends Entity = Entity> = {
  /** Chemin racine exposé (ex. `/api/v1/records`) — pour construire les sous-ressources. */
  base: string;
  list: (params?: ListParams) => Promise<PaginatedEnvelope<T>>;
  show: (id: number | string) => Promise<ItemEnvelope<T>>;
  create: (payload: Record<string, unknown>) => Promise<ItemEnvelope<T>>;
  update: (id: number | string, payload: Record<string, unknown>) => Promise<ItemEnvelope<T>>;
  destroy: (id: number | string) => Promise<unknown>;
  /** Exécute une action métier `POST /{base}/{id}/{verbe}`. */
  action: (id: number | string, verb: string, payload?: Record<string, unknown>) => Promise<unknown>;
  /** Action sans id (ex. `GET /{base}/list`, `POST /{base}/store`). */
  actionCollection: (verb: string, payload?: Record<string, unknown>) => Promise<unknown>;
};

/**
 * Fabrique universelle d'un client CRUD pour une ressource REST (`/api/v1/{base}`).
 * C'est LE « modèle universel » côté données : tout écran Next consomme une
 * instance de `ResourceApi` décrite par une config (voir `lib/crud/types.ts`).
 */
export function createResourceApi<T extends Entity = Entity>(base: string): ResourceApi<T> {
  const root = `/api/v1/${base}`;

  return {
    base: root,
    list: (params) => apiFetch<PaginatedEnvelope<T>>(`${root}${toQuery(params)}`),
    show: (id) => apiFetch<ItemEnvelope<T>>(`${root}/${id}`),
    create: (payload) => apiFetch<ItemEnvelope<T>>(root, { method: 'POST', body: payload }),
    update: (id, payload) => apiFetch<ItemEnvelope<T>>(`${root}/${id}`, { method: 'PUT', body: payload }),
    destroy: (id) => apiFetch<unknown>(`${root}/${id}`, { method: 'DELETE' }),
    action: (id, verb, payload) =>
      apiFetch<unknown>(`${root}/${id}/${verb}`, payload ? { method: 'POST', body: payload } : { method: 'POST' }),
    actionCollection: (verb, payload) =>
      apiFetch<unknown>(`${root}/${verb}`, payload ? { method: 'POST', body: payload } : { method: 'POST' }),
  };
}

/** API publique (portail OPAC) — préfixe différent (`/api/public`). */
export function createPublicResourceApi<T extends Entity = Entity>(base: string): ResourceApi<T> {
  const root = `/api/public/${base}`;

  return {
    base: root,
    list: (params) => apiFetch<PaginatedEnvelope<T>>(`${root}${toQuery(params)}`),
    show: (id) => apiFetch<ItemEnvelope<T>>(`${root}/${id}`),
    create: (payload) => apiFetch<ItemEnvelope<T>>(root, { method: 'POST', body: payload }),
    update: (id, payload) => apiFetch<ItemEnvelope<T>>(`${root}/${id}`, { method: 'PUT', body: payload }),
    destroy: (id) => apiFetch<unknown>(`${root}/${id}`, { method: 'DELETE' }),
    action: (id, verb, payload) =>
      apiFetch<unknown>(`${root}/${id}/${verb}`, payload ? { method: 'POST', body: payload } : { method: 'POST' }),
    actionCollection: (verb, payload) =>
      apiFetch<unknown>(`${root}/${verb}`, payload ? { method: 'POST', body: payload } : { method: 'POST' }),
  };
}
