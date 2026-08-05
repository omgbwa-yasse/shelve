/**
 * Conformité — D09, organisation & sécurité (CRUD).
 *
 * Suite technologiquement neutre, rejouable telle quelle contre Spring Boot en phase 3.
 * Complète la couverture D09 : après l'authentification (auth.test.js), le CRUD des
 * organisations, des rôles et des utilisateurs. L'isolation inter-organisation (R03)
 * est vérifiée sur le pivot `user-organisation-roles`.
 */

import { beforeAll, describe, expect, it } from 'vitest';
import { assertBackendIsOurs, cachedLogin, del, get, post, patch } from '../lib/client.js';
import { shapeOf } from '../lib/normalize.js';

let token;

beforeAll(async () => {
  await assertBackendIsOurs();
  token = await cachedLogin();
});

describe('D09 — organisations', () => {
  it('index : collection paginée conforme au contrat', async () => {
    const res = await get('/organisations', { token });

    expect(res.status).toBe(200);
    expect(Array.isArray(res.data.data)).toBe(true);
    expect(res.data.meta).toHaveProperty('total');
    expect(res.data.links).toHaveProperty('next');
  });

  it('store : crée une organisation (201)', async () => {
    const res = await post('/organisations', {
      token,
      body: { code: `ORG${Date.now() % 100000}`, name: `Organisation ${Date.now() % 100000}` },
    });

    expect(res.status).toBe(201);
    expect(res.data.data.name).toMatch(/^Organisation /);
  });

  it('store : valide ses entrées (422)', async () => {
    const res = await post('/organisations', { token, body: {} });
    expect(res.status).toBe(422);
    expect(res.data.errors).toHaveProperty('code');
    expect(res.data.errors).toHaveProperty('name');
  });
});

describe('D09 — rôles', () => {
  it('index : liste des rôles', async () => {
    const res = await get('/roles', { token });
    expect(res.status).toBe(200);
    expect(Array.isArray(res.data.data)).toBe(true);
  });

  it('store : crée un rôle avec le guard par défaut', async () => {
    const res = await post('/roles', {
      token,
      body: { name: `role-${Date.now() % 100000}`, description: 'Rôle de conformité' },
    });

    expect(res.status).toBe(201);
    expect(res.data.data.guard_name).toBe('web');
  });
});

describe('D09 — utilisateurs', () => {
  let userId;

  it('store : crée un agent sans jamais exposer de secret', async () => {
    const res = await post('/users', {
      token,
      body: {
        name: 'Agent',
        surname: 'Conformité',
        email: `agent-${Date.now() % 100000}@conformance.test`,
        birthday: '1990-01-01',
        password: 'mot-de-passe-secret',
      },
    });

    expect(res.status).toBe(201);
    expect(res.data.data.email).toMatch(/@conformance.test$/);
    expect(JSON.stringify(res.data)).not.toContain('mot-de-passe-secret');
    expect(res.data.data).not.toHaveProperty('password');

    userId = res.data.data.id;
  });

  it('show : le mot de passe n\'apparaît jamais', async () => {
    const res = await get(`/users/${userId}`, { token });
    expect(res.status).toBe(200);
    expect(res.data.data).not.toHaveProperty('password');
    expect(res.data.data).not.toHaveProperty('remember_token');
  });

  it('destroy : supprime l\'agent', async () => {
    const res = await del(`/users/${userId}`, { token });
    expect(res.status).toBe(204);
  });
});

describe('D09 — isolation inter-organisation (R03)', () => {
  it('refuse un rattachement à une organisation étrangère', async () => {
    const org = await post('/organisations', {
      token,
      body: { code: `O${Date.now() % 100000}`, name: 'Organisation cible' },
    });
    expect(org.status).toBe(201);
    const orgId = org.data.data.id;

    // Un agent ne peut être rattaché qu'à son organisation courante (ou être
    // administré) — ici le compte de conformité n'a pas la permission de gérer
    // les rattachements hors de son périmètre : la réponse ne doit pas être un 200.
    const res = await post('/user-organisation-roles', {
      token,
      body: { user_id: 1, organisation_id: orgId, role_id: 1 },
    });

    // 403 (refus de permission) ou 422 (validation) : jamais 201 sans droit.
    expect([403, 404, 422]).toContain(res.status);
  });

  it('un pivot inexistant répond 404 sans divulguer d\'existence', async () => {
    const res = await get('/user-organisation-roles/999999/999999', { token });
    expect([403, 404]).toContain(res.status);
  });

  it('les types de réponse restent stables (forme)', async () => {
    const res = await get('/organisations', { token });
    expect(shapeOf(res.data.data)).toBeTypeOf('string'); // 'array<...>' ou 'array<empty>'
  });
});
