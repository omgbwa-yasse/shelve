import type { FeatureRoute } from '@/lib/routing';
import { routes as mailsRoutes } from './mails/pages';
import { routes as workflowRoutes } from './workflow/pages';
import { routes as workplacesRoutes } from './workplaces/pages';
import { routes as chatsRoutes } from './chats/pages';
import { routes as recordsRoutes } from './records/pages';
import { routes as communicationsRoutes } from './communications/pages';
import { routes as transferringsRoutes } from './transferrings/pages';
import { routes as depositsRoutes } from './deposits/pages';
import { routes as toolsRoutes } from './tools/pages';
import { routes as dolliesRoutes } from './dollies/pages';
import { routes as contactsRoutes } from './contacts/pages';
import { routes as publicRoutes } from './public/pages';
import { routes as aiRoutes } from './ai/pages';
import { routes as settingsRoutes } from './settings/pages';

/**
 * Toutes les routes déclarées par les features. Chaque feature possède SES
 * écrans (List/Detail/Form) — ce fichier ne fait que les rassembler.
 */
export const featureRoutes: FeatureRoute[] = [
  ...mailsRoutes,
  ...workflowRoutes,
  ...workplacesRoutes,
  ...chatsRoutes,
  ...recordsRoutes,
  ...communicationsRoutes,
  ...transferringsRoutes,
  ...depositsRoutes,
  ...toolsRoutes,
  ...dolliesRoutes,
  ...contactsRoutes,
  ...publicRoutes,
  ...aiRoutes,
  ...settingsRoutes,
];

/**
 * Résout la route de feature dont le chemin (ou un alias) est préfixe du chemin
 * demandé (correspondance la plus longue).
 */
export function resolveFeatureRoute(fullPath: string): { route: FeatureRoute; base: string } | undefined {
  let best: { route: FeatureRoute; base: string } | undefined;

  for (const route of featureRoutes) {
    for (const candidate of [route.path, ...(route.aliases ?? [])]) {
      if (candidate === fullPath || fullPath.startsWith(`${candidate}/`)) {
        if (!best || candidate.length > best.base.length) {
          best = { route, base: candidate };
        }
      }
    }
  }

  return best;
}
