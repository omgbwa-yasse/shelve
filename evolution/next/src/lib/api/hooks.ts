'use client';

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity, ItemEnvelope, ListParams, PaginatedEnvelope } from '@/lib/api/types';

/**
 * Hooks de données génériques (infrastructure) — chaque feature les utilise
 * dans SES propres écrans. Pas de rendu ici.
 */

export function useResourceList<T extends Entity = Entity>(
  api: ResourceApi<T>,
  key: string,
  params: ListParams = {},
  enabled = true,
) {
  return useQuery({
    queryKey: [key, params],
    enabled,
    queryFn: () => api.list(params) as Promise<PaginatedEnvelope<T>>,
  });
}

export function useResource<T extends Entity = Entity>(api: ResourceApi<T>, key: string, id?: string | number) {
  return useQuery({
    queryKey: [key, id],
    enabled: id !== undefined && id !== null && id !== '',
    queryFn: async () => (await api.show(id as string | number)) as ItemEnvelope<T>,
  });
}

export function useCreate<T extends Entity = Entity>(api: ResourceApi<T>, key: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (payload: Record<string, unknown>) => api.create(payload) as Promise<ItemEnvelope<T>>,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [key] }),
  });
}

export function useUpdate<T extends Entity = Entity>(api: ResourceApi<T>, key: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, payload }: { id: string | number; payload: Record<string, unknown> }) =>
      api.update(id, payload) as Promise<ItemEnvelope<T>>,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [key] }),
  });
}

export function useDestroy(api: ResourceApi, key: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (id: string | number | undefined) => {
      if (id === undefined || id === null) return Promise.resolve(undefined);
      return api.destroy(id);
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [key] }),
  });
}

export function useAction(api: ResourceApi, key: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: ({ id, verb, payload }: { id: string | number; verb: string; payload?: Record<string, unknown> }) =>
      api.action(id, verb, payload),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: [key] }),
  });
}
