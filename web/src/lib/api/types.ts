/**
 * Contrat API — enveloppes de réponse de l'API Laravel v1.
 *
 * L'enveloppe des API Resources Laravel est `{ data, meta, links }` (pagination)
 * ou `{ data }` (objet unique). `schema.d.ts` (généré depuis openapi.yaml)
 * affinera ces types à terme ; ici on garde un contrat minimal mais typé.
 */

/** Enveloppe d'un objet unique. */
export type ItemEnvelope<T> = {
  data: T;
};

/** Enveloppe d'une liste paginée (API Resources Laravel). */
export type PaginatedEnvelope<T> = {
  data: T[];
  meta?: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
    from: number;
    to: number;
  };
  links?: {
    first?: string | null;
    last?: string | null;
    prev?: string | null;
    next?: string | null;
  };
};

/** Enveloppe d'une liste non paginée. */
export type ListEnvelope<T> = {
  data: T[];
};

/** Erreur d'API (422 champ par champ, 403, 404, 409, …). */
export type ApiErrorPayload = {
  message?: string;
  errors?: Record<string, string[]>;
};

/** Entité générique — tout objet renvoyé par l'API. */
export type Entity = Record<string, unknown> & { id?: number | string };

/** Paramètres de liste partagés (pagination, tri, recherche). */
export type ListParams = {
  page?: number;
  per_page?: number;
  search?: string;
  [key: string]: string | number | boolean | undefined;
};
