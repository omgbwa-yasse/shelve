import type { ComponentType } from 'react';

/**
 * Route d'une feature — chaque module déclare SES écrans (pas de moteur CRUD
 * partagé). Le routeur dispatche : `/x` → List, `/x/create` → Form(create),
 * `/x/{id}` → Detail, `/x/{id}/edit` → Form(edit).
 */
export type FeatureRoute = {
  /** Chemin racine (ex. `/mails`) ou chemin d'écran spécialisé (ex. `/workflow/dashboard`). */
  path: string;
  /** Chemins alternatifs (doublons settings/tools). */
  aliases?: string[];
  /** Écran liste (ou écran unique pour les routes spécialisées). */
  List: ComponentType;
  /** Écran détail (reçoit `{ id }`). */
  Detail?: ComponentType<{ id: string }>;
  /** Écran création/édition (reçoit `{ mode, id? }`). */
  Form?: ComponentType<{ mode: 'create' | 'edit'; id?: string }>;
};
