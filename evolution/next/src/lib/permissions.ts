/**
 * Miroir des Gates Laravel (`app/Policies/*`) — sert UNIQUEMENT à
 * masquer/désactiver des éléments d'UI (ergonomie). La protection réelle reste
 * côté serveur ; ne jamais s'appuyer sur ce fichier comme seule barrière
 * (voir PHASE-2-NEXTJS.md, étape 2.1 point g).
 */
export function can(permissions: string[], permission: string): boolean {
  return permissions.includes(permission);
}
