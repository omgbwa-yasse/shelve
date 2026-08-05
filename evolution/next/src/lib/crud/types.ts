import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { ComponentType } from 'react';

/**
 * MODÈLE UNIVERSEL — configuration déclarative d'un écran CRUD.
 *
 * Un `ResourceConfig` décrit TOUT ce dont un écran a besoin (liste, formulaire,
 * détail, actions, autorisation) pour une ressource de l'API. Les composants
 * génériques de `components/crud/*` savent le rendre. Créer un écran = ajouter
 * une config au registre (`lib/crud/registry.ts`), jamais écrire un composant.
 */

export type FieldType =
  | 'text'
  | 'textarea'
  | 'number'
  | 'date'
  | 'datetime'
  | 'boolean'
  | 'select'
  | 'reference'
  | 'email'
  | 'url'
  | 'password';

export type Option = { value: string | number; label: string };

/** Champ de formulaire (création / édition) — mappé sur un champ de l'API. */
export type Field = {
  name: string;
  label: string;
  type?: FieldType;
  required?: boolean;
  placeholder?: string;
  help?: string;
  /** Options pour `select` (statique). */
  options?: Option[];
  /** Résolution des options pour `reference` (liste issue de l'API). */
  reference?: {
    api: ResourceApi;
    valueKey: string;
    labelKey: string;
    /** API de recherche à la volée (optionnel). */
    search?: (query: string) => Promise<unknown[]>;
  };
  /** Clé de libellé si `type: 'reference'` — sinon la valeur brute est stockée. */
  displayKey?: string;
  /** Visible en édition uniquement (readonly à l'édition ?). */
  readonly?: boolean;
  /** Masqué dans le formulaire (champs gérés par le serveur). */
  hidden?: boolean;
  /** Validation zod client (miroir de la validation serveur). */
  rules?: (value: unknown) => string | null;
  /** Colonne (largeur) tailwind pour le rendu grille. */
  colSpan?: 1 | 2 | 3 | 4;
};

/** Colonne d'affichage en liste. */
export type Column = {
  key: string;
  label: string;
  /** Accès à la valeur (chemin `a.b.c` ou fonction). */
  accessor?: (row: Entity) => unknown;
  /** Rend non trivial (badge, icône, date formatée…). */
  render?: (value: unknown, row: Entity) => React.ReactNode;
  /** Triable côté serveur (paramètre `sort_by`). */
  sortable?: boolean;
  /** Champ utilisé pour la recherche serveur. */
  searchKey?: string;
  /** Classes tailwind. */
  className?: string;
};

/** Action métier affichée sous forme de bouton. */
export type ActionSpec = {
  label: string;
  verb?: string;
  /** Endpoint action : `api.action(id, verb)`. */
  method?: 'action' | 'destroy' | 'navigate' | 'custom';
  href?: string;
  confirm?: string;
  icon?: string;
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
  /** Masquée selon les permissions de l'utilisateur. */
  permission?: string;
  /** Appelée après succès (invalidation de cache). */
  invalidate?: string[];
};

/** Filtre affiché au-dessus de la liste. */
export type FilterSpec = {
  name: string;
  label: string;
  type?: 'text' | 'select' | 'date' | 'boolean';
  options?: Option[];
};

export type ResourceConfig = {
  /** Chemin Next racine de la ressource (ex. `/workflow/definitions`). */
  path: string;
  /** Chemins alternatifs pointant vers la même ressource (ex. doublons settings/tools). */
  aliases?: string[];
  /** Libellé de la ressource (singulier). */
  label: string;
  /** Libellé pluriel. */
  plural: string;
  /** Description courte affichée sous le titre. */
  description?: string;
  /** Client API. */
  api: ResourceApi;
  /** Chemin d'accès à la collection dans l'enveloppe paginée (défaut `data`). */
  dataPath?: string;
  /** Colonnes de liste. */
  columns: Column[];
  /** Champs du formulaire création/édition. */
  fields: Field[];
  /** Champs visibles sur la fiche détail (défaut : tous les champs). */
  detailFields?: string[];
  /** Filtres au-dessus de la liste. */
  filters?: FilterSpec[];
  /** Actions de ligne. */
  rowActions?: ActionSpec[];
  /** Actions de masse / de page (haut de liste). */
  pageActions?: ActionSpec[];
  /** Clé du champ affiché comme identité (titre de la fiche). */
  titleKey?: string;
  /** Clé du champ code/badge (affiché en gras dans la liste). */
  codeKey?: string;
  /** Rendre un lien vers le détail dans la liste. */
  linkable?: boolean;
  /** Permission d'accès (masque l'entrée si absente). */
  permission?: string;
  /** Affiche le bouton créer. */
  creatable?: boolean;
  /** Affiche le bouton éditer. */
  editable?: boolean;
  /** Affiche le bouton supprimer. */
  deletable?: boolean;
  /** Affiche le bouton exporter (lien). */
  exportable?: boolean;
  /** Sous-ressources navigables (onglets de la fiche). */
  tabs?: Tab[];
};

/** Onglet de sous-ressource sur une fiche détail (ex. records/{id}/children). */
export type Tab = {
  key: string;
  label: string;
  /** Chemin API de la sous-ressource relatif à la ressource parente. */
  apiPath?: string;
  /** Client API dédié (sinon construit depuis `apiPath`). */
  api?: ResourceApi;
  columns: Column[];
  /** Champs du formulaire d'ajout en ligne (optionnel). */
  fields?: Field[];
  /** Client API de la sous-ressource parente (pour construire le chemin). */
  parentApi?: ResourceApi;
  /** Endpoint d'ajout : `action(parentId, verb, payload)`. */
  addVerb?: string;
  /** Endpoint de suppression : `destroy(id)` sur la sous-ressource. */
  deletable?: boolean;
};

/** Route d'écran spécialisé (tableau de bord, import/export, arborescence…). */
export type SpecialRoute = {
  /** Préfixe de chemin. Si `exact`, le chemin doit être identique. */
  path: string;
  exact?: boolean;
  component: ComponentType<{ action?: string; tab?: string; view?: string }>;
};
