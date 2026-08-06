/**
 * Assistant IA du panneau latéral (Chat / Routine / Historique) — voir
 * `routes/api/D18.php` côté Laravel. `routinesApi` suit le contrat CRUD
 * générique (paginé) ; les conversations ont un cycle de vie propre
 * (premier message à la création, messages suivants via `sendMessage`) donc
 * des fetchers dédiés plutôt que `createResourceApi`.
 */
import { apiFetch } from '@/lib/api/client';
import { createResourceApi } from '@/lib/api/resources';
import type { Entity, ItemEnvelope, ListEnvelope } from '@/lib/api/types';

export const routinesApi = createResourceApi('ai/routines');

export type PageContext = {
  path: string;
  search?: string;
};

/**
 * Modes de conversation (voir `App\Models\AiConversation::MODES` côté
 * Laravel) — pilotent le degré de confirmation exigé avant une action de
 * création/modification/suppression dans le prompt système de l'assistant.
 */
export type AssistantMode = 'manuel' | 'edit' | 'plan' | 'autonome';

export const ASSISTANT_MODE_LABELS: Record<AssistantMode, string> = {
  manuel: 'Manuel',
  edit: 'Edit',
  plan: 'Plan',
  autonome: 'Autonome',
};

export const ASSISTANT_MODE_DESCRIPTIONS: Record<AssistantMode, string> = {
  manuel: "Confirmation demandée avant toute création, modification ou suppression.",
  edit: 'Modifications pré-approuvées ; création et suppression restent toujours confirmées.',
  plan: "Ne produit qu'un plan à valider, n'exécute jamais rien.",
  autonome: 'Agit sans confirmation répétée, dans la limite stricte de vos permissions.',
};

/** GET /api/v1/ai/conversations — historique des conversations de l'agent. */
export function getConversations() {
  return apiFetch<ListEnvelope<Entity>>('/api/v1/ai/conversations');
}

/** GET /api/v1/ai/conversations/{id} — conversation avec ses messages. */
export function getConversation(id: string | number) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/ai/conversations/${id}`);
}

/** POST /api/v1/ai/conversations — démarre une conversation avec un premier message. */
export function startConversation(message: string, context?: PageContext, mode?: AssistantMode) {
  return apiFetch<ItemEnvelope<Entity>>('/api/v1/ai/conversations', {
    method: 'POST',
    body: { message, context, mode },
  });
}

/**
 * POST /api/v1/ai/conversations/{id}/messages — poursuit une conversation
 * existante ; passer `mode` change le mode du fil pour la suite des échanges.
 */
export function sendConversationMessage(id: string | number, message: string, context?: PageContext, mode?: AssistantMode) {
  return apiFetch<ItemEnvelope<Entity>>(`/api/v1/ai/conversations/${id}/messages`, {
    method: 'POST',
    body: { message, context, mode },
  });
}

/**
 * DELETE /api/v1/ai/conversations/{id} — malgré le verbe HTTP REST, le
 * serveur archive (`AiConversation::archive()`) et ne supprime jamais :
 * l'historique de chat n'est jamais purgé (exigence utilisateur du
 * 2026-08-05). La conversation disparaît de l'onglet Historique, c'est tout.
 */
export function archiveConversation(id: string | number) {
  return apiFetch<unknown>(`/api/v1/ai/conversations/${id}`, { method: 'DELETE' });
}

/** POST /api/v1/ai/routines/{id}/run — exécution immédiate, hors planification. */
export function runRoutine(id: string | number) {
  return routinesApi.action(id, 'run') as Promise<ItemEnvelope<Entity>>;
}
