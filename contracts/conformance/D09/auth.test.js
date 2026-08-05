/**
 * Conformité — D09, authentification.
 *
 * Cette suite ne connaît ni Laravel ni Spring Boot : elle n'exprime que ce que le
 * contrat exige (contracts/CONVENTIONS.md §4, §5, §6). Elle sera rejouée telle quelle
 * contre Spring Boot en phase 3, sans modification.
 *
 * Prérequis : voir contracts/conformance/README.md.
 */

import { beforeAll, describe, expect, it } from 'vitest';
import { assertBackendIsOurs, cachedLogin, get, login, post } from '../lib/client.js';
import { comparable, matchGolden } from '../lib/golden.js';
import { shapeOf } from '../lib/normalize.js';

const EMAIL = process.env.API_TEST_EMAIL;
const PASSWORD = process.env.API_TEST_PASSWORD;

describe('POST /auth/login', () => {
  beforeAll(assertBackendIsOurs);

  it('renvoie un token porteur et le profil', async () => {
    const res = await post('/auth/login', {
      body: { email: EMAIL, password: PASSWORD, device_name: 'conformance' },
    });

    expect(res.status).toBe(200);
    expect(res.data.data.token).toBeTypeOf('string');
    expect(res.data.data.token_type).toBe('Bearer');
    expect(res.data.data.user.email).toBe(EMAIL);
    expect(Array.isArray(res.data.data.permissions)).toBe(true);

    matchGolden('D09/auth/login-success', comparable(res));
  });

  it('ne divulgue jamais le mot de passe', async () => {
    const res = await post('/auth/login', {
      body: { email: EMAIL, password: PASSWORD },
    });

    expect(JSON.stringify(res.data)).not.toContain('password');
  });

  it('refuse un mot de passe erroné avec 422 et un corps de validation', async () => {
    const res = await post('/auth/login', {
      body: { email: EMAIL, password: 'mot-de-passe-invalide' },
    });

    expect(res.status).toBe(422);
    expect(res.data.errors).toHaveProperty('email');

    matchGolden('D09/auth/login-wrong-password', comparable(res));
  });

  it('ne distingue pas un compte inconnu d\'un mot de passe erroné', async () => {
    // Des réponses différentes permettraient d'énumérer les comptes (CONVENTIONS §6).
    const inconnu = await post('/auth/login', {
      body: { email: 'compte-inexistant@conformance.test', password: 'peu importe' },
    });
    const mauvais = await post('/auth/login', {
      body: { email: EMAIL, password: 'mot-de-passe-invalide' },
    });

    expect(inconnu.status).toBe(mauvais.status);
    expect(inconnu.data.errors.email).toEqual(mauvais.data.errors.email);
  });

  it('valide ses entrées', async () => {
    const vide = await post('/auth/login', { body: {} });
    expect(vide.status).toBe(422);
    expect(Object.keys(vide.data.errors)).toEqual(
      expect.arrayContaining(['email', 'password']),
    );

    const malforme = await post('/auth/login', {
      body: { email: 'pas-une-adresse', password: 'x' },
    });
    expect(malforme.status).toBe(422);
    expect(malforme.data.errors).toHaveProperty('email');
  });
});

describe('GET /auth/me', () => {
  let token;

  beforeAll(async () => {
    token = await cachedLogin();
  });

  it('exige un token', async () => {
    const res = await get('/auth/me');
    expect(res.status).toBe(401);
  });

  it('rejette un token invalide', async () => {
    const res = await get('/auth/me', { token: '999|jetonManifestementInvalide' });
    expect(res.status).toBe(401);
  });

  it('renvoie le profil et les permissions', async () => {
    const res = await get('/auth/me', { token });

    expect(res.status).toBe(200);
    expect(res.data.data.user.email).toBe(EMAIL);
    expect(Array.isArray(res.data.data.permissions)).toBe(true);

    matchGolden('D09/auth/me', comparable(res));
  });

  it('respecte les types du contrat', async () => {
    const res = await get('/auth/me', { token });
    const user = res.data.data.user;

    // CONVENTIONS §5 : booléens en vrais booléens, jamais 0/1.
    expect(user.is_superadmin).toBeTypeOf('boolean');

    // Dates en ISO-8601 UTC, jamais au format local ni SQL.
    for (const champ of ['created_at', 'updated_at']) {
      if (user[champ] !== null && user[champ] !== undefined) {
        expect(user[champ]).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/);
      }
    }

    if (user.birthday) {
      expect(user.birthday).toMatch(/^\d{4}-\d{2}-\d{2}$/);
    }

    matchGolden('D09/auth/me-shape', shapeOf(res.data));
  });
});

describe('POST /auth/logout', () => {
  it('révoque le token et renvoie 204 sans corps', async () => {
    const token = await login();

    const res = await post('/auth/logout', { token });
    expect(res.status).toBe(204);
    expect(res.data).toBeNull();

    // Le token révoqué ne doit plus ouvrir aucune porte.
    const apres = await get('/auth/me', { token });
    expect(apres.status).toBe(401);
  });
});

describe('POST /auth/switch-organisation', () => {
  let token;

  beforeAll(async () => {
    token = await cachedLogin();
  });

  it('exige un token', async () => {
    const res = await post('/auth/switch-organisation', { body: { organisation_id: 1 } });
    expect(res.status).toBe(401);
  });

  it('valide ses entrées', async () => {
    const res = await post('/auth/switch-organisation', {
      token,
      body: { organisation_id: 999999999 },
    });

    expect(res.status).toBe(422);
    expect(res.data.errors).toHaveProperty('organisation_id');
  });

  it('refuse une organisation à laquelle le compte n\'est pas rattaché', async () => {
    // Cœur du risque R03. Un 200 ici signifie que n'importe qui peut se placer dans
    // le contexte d'une autre organisation et lire ses données.
    const orgEtrangere = Number(process.env.API_TEST_FOREIGN_ORG_ID);

    if (!orgEtrangere) {
      // Pas de saut silencieux : une garantie de sécurité non vérifiée doit se voir.
      throw new Error(
        'API_TEST_FOREIGN_ORG_ID non défini — le contrôle d\'isolation R03 ne peut pas être vérifié.',
      );
    }

    const res = await post('/auth/switch-organisation', {
      token,
      body: { organisation_id: orgEtrangere },
    });

    expect(res.status).toBe(403);
  });
});
