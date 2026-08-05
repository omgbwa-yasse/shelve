import path from 'node:path';
import type { NextConfig } from 'next';

/**
 * Aucune URL de backend ne doit apparaître ici : le seul point qui connaît
 * NEXT_PUBLIC_API_BASE_URL est `src/lib/api/client.ts` (voir PHASE-2-NEXTJS.md,
 * "Règle d'architecture non négociable"). Un lint CI vérifie cette contrainte.
 */
const nextConfig: NextConfig = {
  reactStrictMode: true,
  // Les écrans sont pilotés par des chemins dynamiques (routeur universel) :
  // les "typed routes" strictes entrent en conflit avec les href construits à
  // la volée. On garde `false` — la sûreté vient de la résolution par registre.
  typedRoutes: false,
  // Ce sous-projet vit intentionnellement niché dans le dépôt Laravel :
  // évite que Next ne remonte par erreur à la racine du dépôt parent.
  outputFileTracingRoot: path.join(__dirname),
};

export default nextConfig;
