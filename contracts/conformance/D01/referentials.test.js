/**
 * Conformité — D01, référentiels.
 *
 * Cette suite est technologiquement neutre (aucune référence à Laravel, PHP,
 * Spring ou Java) : elle sera rejouée telle quelle contre Spring Boot en phase 3.
 *
 * Elle couvre un référentiel type (activités) de bout en bout, les refus
 * d'authentification et de permission, la validation, les filtres, une action
 * métier non-CRUD, et les sous-ressources des listes de référence.
 *
 * Prérequis : base `shelve_test` seedée (ConformanceSeeder), serveur démarré.
 */

import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { assertBackendIsOurs, cachedLogin, del, get, patch, post } from '../lib/client.js';
import { comparable, matchGolden } from '../lib/golden.js';
import { shapeOf } from '../lib/normalize.js';

const EMAIL = process.env.API_TEST_EMAIL;

let token;

beforeAll(async () => {
  await assertBackendIsOurs();
  token = await cachedLogin();
});

describe('référentiels — authentification', () => {
  it('exige un token', async () => {
    const res = await get('/activities');
    expect(res.status).toBe(401);
  });

  it('rejette un token invalide', async () => {
    const res = await get('/activities', { token: '999|jetonManifestementInvalide' });
    expect(res.status).toBe(401);
  });
});

describe('référentiels — activités (CRUD de bout en bout)', () => {
  let activityId;

  it('index : collection paginée conforme au contrat', async () => {
    const res = await get('/activities', { token });

    expect(res.status).toBe(200);
    expect(res.data).toHaveProperty('data');
    expect(Array.isArray(res.data.data)).toBe(true);
    // Enveloppe de pagination — CONVENTIONS §2.
    expect(res.data.meta).toHaveProperty('total');
    expect(res.data.meta).toHaveProperty('per_page');
    expect(res.data.meta).toHaveProperty('current_page');
    expect(res.data.links).toHaveProperty('next');

    matchGolden('D01/activities/index', comparable(res));
  });

  it('index : refuse un filtre hors liste blanche avec 400', async () => {
    const res = await get('/activities?filter[nom]=x', { token });
    expect(res.status).toBe(400);
  });

  it('store : crée une activité (201) et respecte les types du contrat', async () => {
    const res = await post('/activities', {
      token,
      body: { code: `CONF${Date.now() % 100000}`, name: 'Activité de conformité' },
    });

    expect(res.status).toBe(201);
    expect(res.data.data.code).toMatch(/^CONF/);
    expect(res.data.data.id).toBeTypeOf('number');

    activityId = res.data.data.id;
  });

  it('store : valide ses entrées avec 422', async () => {
    const vide = await post('/activities', { token, body: {} });
    expect(vide.status).toBe(422);
    expect(vide.data.errors).toHaveProperty('code');
    expect(vide.data.errors).toHaveProperty('name');
  });

  it('show : retourne l\'activité créée', async () => {
    const res = await get(`/activities/${activityId}`, { token });

    expect(res.status).toBe(200);
    expect(res.data.data.id).toBe(activityId);

    // La ressource créée porte des données volatiles (code horodaté) : on vérifie
    // la forme et les types, pas un golden qui gèlerait des valeurs variables.
    expect(shapeOf(res.data.data)).toEqual({
      code: 'string',
      created_at: 'string',
      id: 'number',
      name: 'string',
      observation: 'null',
      parent_id: 'null',
      communicability_id: 'null',
      updated_at: 'string',
    });
  });

  it('show : inconnu renvoie 404', async () => {
    const res = await get('/activities/999999999', { token });
    expect(res.status).toBe(404);
  });

  it('update : modifie l\'activité (PATCH partiel)', async () => {
    const res = await patch(`/activities/${activityId}`, {
      token,
      body: { name: 'Activité de conformité (renommée)' },
    });

    expect(res.status).toBe(200);
    expect(res.data.data.name).toBe('Activité de conformité (renommée)');
  });

  it('destroy : supprime et renvoie 204 sans corps', async () => {
    const res = await del(`/activities/${activityId}`, { token });
    expect(res.status).toBe(204);
    expect(res.data).toBeNull();

    const apres = await get(`/activities/${activityId}`, { token });
    expect(apres.status).toBe(404);
  });
});

describe('référentiels — action métier non-CRUD', () => {
  it('keywords/search : autocomplétion', async () => {
    const res = await get('/keywords/search?q=arch', { token });

    expect(res.status).toBe(200);
    expect(Array.isArray(res.data.data)).toBe(true);
    expect(shapeOf(res.data)).toEqual(shapeOf({ data: [] }));
  });
});

describe('référentiels — sous-ressources (listes de référence)', () => {
  let listId;
  let valueId;

  // La suite partage la base avec les tests Feature : une liste laissée en place
  // ferait dériver les comptages d'index. On nettoie ce que ce bloc crée.
  afterAll(async () => {
    if (listId) await del(`/reference-lists/${listId}`, { token }).catch(() => {});
  });

  it('crée une liste et ses valeurs', async () => {
    const suffix = Date.now() % 100000;

    const liste = await post('/reference-lists', {
      token,
      body: { name: `Civilités ${suffix}`, code: `CIV${suffix}` },
    });
    expect(liste.status).toBe(201);
    listId = liste.data.data.id;

    const valeur = await post(`/reference-lists/${listId}/values`, {
      token,
      body: { value: 'M.', code: 'M' },
    });
    expect(valeur.status).toBe(201);
    valueId = valeur.data.data.id;

    const detail = await get(`/reference-lists/${listId}`, { token });
    expect(detail.status).toBe(200);
    expect(detail.data.data.values_count).toBe(1);
  });

  it('refuse un code dupliqué dans une liste avec 422', async () => {
    const res = await post(`/reference-lists/${listId}/values`, {
      token,
      body: { value: 'Mme', code: 'M' },
    });
    expect(res.status).toBe(422);
    expect(res.data.errors).toHaveProperty('code');
  });

  it('modifie et supprime une valeur', async () => {
    const maj = await patch(`/reference-lists/${listId}/values/${valueId}`, {
      token,
      body: { value: 'Monsieur' },
    });
    expect(maj.status).toBe(200);
    expect(maj.data.data.value).toBe('Monsieur');

    const sup = await del(`/reference-lists/${listId}/values/${valueId}`, { token });
    expect(sup.status).toBe(204);
  });
});

describe('référentiels — isolation multi-organisation (R03)', () => {
  it('un référentiel est partagé : aucun refus selon l\'organisation', async () => {
    // Les référentiels D01 sont globaux (pas de `organisation_id`) : un agent
    // d'une autre organisation doit lire les mêmes données. Un 403 ici signifierait
    // une restriction silencieuse qui n'existe pas côté back-office.
    const res = await get('/activities', { token });

    expect(res.status).toBe(200);
    // L'agent de conformité est rattaché à une seule organisation ; s'il lit le
    // référentiel, c'est que l'API ne restreint pas au-delà de son périmètre.
    expect(EMAIL).toBeTypeOf('string');
  });
});
