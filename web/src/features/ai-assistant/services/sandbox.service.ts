/**
 * Client du sandbox Python de l'assistant IA (D14).
 *
 * Endpoints : POST /api/v1/ai/sandboxes (open), POST /{id}/files (write),
 * POST /{id}/run, POST /{id}/close, GET /{id}/files, GET /{id}/files/{f}/download,
 * GET /ai/sandboxes/capabilities.
 */
import { apiFetch } from '@/lib/api/client';
import type { Entity, ItemEnvelope, ListEnvelope } from '@/lib/api/types';

export type SandboxStatus = 'created' | 'running' | 'success' | 'error' | 'expired';

export type SandboxFile = Entity & {
  id: number;
  sandbox_id: number;
  section: 'input' | 'core' | 'reference' | 'output' | 'logs';
  path: string;
  name: string;
  size: number;
  mime?: string;
  hash?: string;
};

export type SandboxCapability = {
  id: string;
  title: string;
  input?: string;
  libraries?: string[];
  output?: string;
  example?: string;
};

/** GET /api/v1/ai/sandboxes/capabilities — catalogue des capacités du sandbox. */
export function getSandboxCapabilities() {
  return apiFetch<ItemEnvelope<{ runtime: string; capabilities: SandboxCapability[] }>>(
    '/api/v1/ai/sandboxes/capabilities',
  );
}

/** POST /api/v1/ai/sandboxes — ouvre un sandbox. */
export function openSandbox(payload: { name?: string; conversation_id?: number; pattern?: string }) {
  return apiFetch<ItemEnvelope<Entity>>('/api/v1/ai/sandboxes', { method: 'POST', body: payload });
}

/** POST /api/v1/ai/sandboxes/{id}/files — écrit un fichier. */
export function writeSandboxFile(
  id: number | string,
  payload: { section: string; path: string; content: string },
) {
  return apiFetch<ItemEnvelope<SandboxFile>>(`/api/v1/ai/sandboxes/${id}/files`, {
    method: 'POST',
    body: payload,
  });
}

/** POST /api/v1/ai/sandboxes/{id}/run — exécute un script. */
export function runSandboxScript(id: number | string, script: string) {
  return apiFetch<ItemEnvelope<{ exit_code: number; status: string; output?: string; error?: string }>>(
    `/api/v1/ai/sandboxes/${id}/run`,
    { method: 'POST', body: { script } },
  );
}

/** POST /api/v1/ai/sandboxes/{id}/close — clôture et récupère les fichiers. */
export function closeSandbox(id: number | string) {
  return apiFetch<ItemEnvelope<{ closed: boolean; files: SandboxFile[] }>>(
    `/api/v1/ai/sandboxes/${id}/close`,
    { method: 'POST' },
  );
}

/** GET /api/v1/ai/sandboxes/{id}/files — liste les fichiers d'un sandbox. */
export function getSandboxFiles(id: number | string) {
  return apiFetch<ListEnvelope<SandboxFile>>(`/api/v1/ai/sandboxes/${id}/files`);
}

/**
 * URL de téléchargement d'un fichier produit — passe par le proxy Next.
 * Le token est porté par le cookie httpOnly côté serveur.
 */
export function sandboxFileDownloadUrl(sandboxId: number | string, fileId: number | string) {
  return `/api/proxy/api/v1/ai/sandboxes/${sandboxId}/files/${fileId}/download`;
}
