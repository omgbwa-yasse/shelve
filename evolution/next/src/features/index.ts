/**
 * AgréGATEUR des features — chaque module possède sa propre config (types,
 * services API, composants, ressources). Ce fichier est le SEUL point qui
 * les rassemble pour le routeur universel.
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { resources as mailsResources, specialRoutes as mailsSpecial } from './mails/resources';
import { resources as workflowResources, specialRoutes as workflowSpecial } from './workflow/resources';
import { resources as workplacesResources, specialRoutes as workplacesSpecial } from './workplaces/resources';
import { resources as chatsResources, specialRoutes as chatsSpecial } from './chats/resources';
import { resources as recordsResources, specialRoutes as recordsSpecial } from './records/resources';
import { resources as communicationsResources, specialRoutes as communicationsSpecial } from './communications/resources';
import { resources as transferringsResources, specialRoutes as transferringsSpecial } from './transferrings/resources';
import { resources as depositsResources, specialRoutes as depositsSpecial } from './deposits/resources';
import { resources as toolsResources, specialRoutes as toolsSpecial } from './tools/resources';
import { resources as dolliesResources, specialRoutes as dolliesSpecial } from './dollies/resources';
import { resources as contactsResources, specialRoutes as contactsSpecial } from './contacts/resources';
import { resources as publicResources, specialRoutes as publicSpecial } from './public/resources';
import { resources as aiResources, specialRoutes as aiSpecial } from './ai/resources';
import { resources as settingsResources, specialRoutes as settingsSpecial } from './settings/resources';

export const featureResources: ResourceConfig[] = [
  ...mailsResources,
  ...workflowResources,
  ...workplacesResources,
  ...chatsResources,
  ...recordsResources,
  ...communicationsResources,
  ...transferringsResources,
  ...depositsResources,
  ...toolsResources,
  ...dolliesResources,
  ...contactsResources,
  ...publicResources,
  ...aiResources,
  ...settingsResources,
];

export const featureSpecialRoutes: SpecialRoute[] = [
  ...mailsSpecial,
  ...workflowSpecial,
  ...workplacesSpecial,
  ...chatsSpecial,
  ...recordsSpecial,
  ...communicationsSpecial,
  ...transferringsSpecial,
  ...depositsSpecial,
  ...toolsSpecial,
  ...dolliesSpecial,
  ...contactsSpecial,
  ...publicSpecial,
  ...aiSpecial,
  ...settingsSpecial,
];

/** Résout un écran spécialisé (préfixe ou exact) pour un chemin. */
export function getFeatureSpecialRoute(path: string) {
  for (const route of featureSpecialRoutes) {
    if (route.exact ? path === route.path : path.startsWith(route.path)) {
      return route.component;
    }
  }
  return undefined;
}

/** Résout la config CRUD dont le chemin est préfixe du chemin complet. */
export function resolveFeatureConfig(fullPath: string): ResourceConfig | undefined {
  let best: ResourceConfig | undefined;

  for (const r of featureResources) {
    for (const c of [r.path, ...(r.aliases ?? [])]) {
      if (c === fullPath || fullPath.startsWith(`${c}/`)) {
        if (!best || c.length > best.path.length) best = r;
      }
    }
  }

  return best;
}
