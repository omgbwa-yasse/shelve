/**
 * Conformité — D03, localisation physique.
 *
 * Suite technologiquement neutre, rejouable telle quelle contre Spring Boot en phase 3.
 * Couvre un référentiel global (bâtiments) et le cycle org-scopé salles → rayonnages
 * → conteneurs, avec le contrôle d'isolation inter-organisation (R03).
 *
 * La suite nettoie TOUT ce qu'elle crée (bâtiment, étage, salle, rayonnage, conteneur,
 * statuts et types de conteneurs) : les golden files d'index restent stables d'une
 * exécution à l'autre.
 */

import { afterAll, beforeAll, describe, expect, it } from 'vitest';
import { assertBackendIsOurs, cachedLogin, del, get, post, patch } from '../lib/client.js';
import { comparable, matchGolden } from '../lib/golden.js';
import { shapeOf } from '../lib/normalize.js';

let token;
const created = [];

async function track(type, id) {
  if (id !== undefined && id !== null) created.push({ type, id });
}

async function cleanup() {
  // Suppression dans l'ordre inverse de création (contraintes FK).
  for (const { type, id } of [...created].reverse()) {
    const path =
      type === 'container' ? `/containers/${id}`
      : type === 'shelf' ? `/shelves/${id}`
      : type === 'room' ? `/rooms/${id}`
      : type === 'floor' ? `/floors/${id}`
      : type === 'status' ? `/container-statuses/${id}`
      : type === 'property' ? `/container-properties/${id}`
      : `/buildings/${id}`;
    await del(path, { token }).catch(() => {});
  }
  created.length = 0;
}

beforeAll(async () => {
  await assertBackendIsOurs();
  token = await cachedLogin();
});

afterAll(cleanup);

describe('D03 — bâtiments (référentiel global)', () => {
  it('exige un token', async () => {
    const res = await get('/buildings');
    expect(res.status).toBe(401);
  });

  it('index : collection paginée conforme au contrat', async () => {
    const res = await get('/buildings', { token });

    expect(res.status).toBe(200);
    expect(Array.isArray(res.data.data)).toBe(true);
    expect(res.data.meta).toHaveProperty('total');
    expect(res.data.links).toHaveProperty('next');

    // La forme est stable même si le jeu de données évolue ; le golden fige
    // l'enveloppe de pagination vide de la base seedée.
    matchGolden('D03/buildings/index-shape', shapeOf(res.data));
  });

  it('store : crée un bâtiment (201)', async () => {
    const res = await post('/buildings', {
      token,
      body: { name: `Site ${Date.now() % 100000}`, visibility: 'public' },
    });

    expect(res.status).toBe(201);
    expect(res.data.data.name).toMatch(/^Site /);
    expect(res.data.data.is_public).toBe(true);
    await track('building', res.data.data.id);
  });

  it('store : valide ses entrées (422)', async () => {
    const res = await post('/buildings', { token, body: { name: 'X', visibility: 'inconnu' } });
    expect(res.status).toBe(422);
    expect(res.data.errors).toHaveProperty('visibility');
  });
});

describe('D03 — salles et localisation (org-scopé, R03)', () => {
  it('crée une salle rattachée à l\'organisation de l\'agent', { timeout: 30000 }, async () => {
    const suffix = Date.now() % 100000;

    const building = await post('/buildings', { token, body: { name: `Site ${suffix}`, visibility: 'public' } });
    expect(building.status).toBe(201);
    await track('building', building.data.data.id);

    const floor = await post('/floors', { token, body: { name: `RDC ${suffix}`, building_id: building.data.data.id } });
    expect(floor.status).toBe(201);
    await track('floor', floor.data.data.id);

    const room = await post('/rooms', {
      token,
      body: { code: `R${suffix % 100000}`, name: `Salle ${suffix}`, visibility: 'public', type: 'archives', floor_id: floor.data.data.id },
    });
    expect(room.status).toBe(201);
    await track('room', room.data.data.id);

    const shelf = await post('/shelves', {
      token,
      body: { code: `S${suffix % 100000}`, face: 2, ear: 2, shelf: 3, shelf_length: 100, room_id: room.data.data.id },
    });
    expect(shelf.status).toBe(201);
    await track('shelf', shelf.data.data.id);

    const status = await post('/container-statuses', { token, body: { name: `Statut ${suffix}` } });
    expect(status.status).toBe(201);
    await track('status', status.data.data.id);

    const property = await post('/container-properties', { token, body: { name: `Type ${suffix}`, width: 10, length: 20, depth: 30 } });
    expect(property.status).toBe(201);
    await track('property', property.data.data.id);

    const container = await post('/containers', {
      token,
      body: {
        code: `C${suffix % 100000}`,
        shelve_id: shelf.data.data.id,
        status_id: status.data.data.id,
        property_id: property.data.data.id,
      },
    });
    expect(container.status).toBe(201);
    await track('container', container.data.data.id);
  });

  it('un conteneur d\'une autre organisation est 404 (isolation R03)', async () => {
    const res = await get('/containers/999999999', { token });
    expect(res.status).toBe(404);
  });

  it('show : le rayonnage expose les champs calculés du contrat', { timeout: 15000 }, async () => {
    const shelf = created.find((c) => c.type === 'shelf');
    const res = await get(`/shelves/${shelf.id}`, { token });

    expect(res.status).toBe(200);
    // Capacité = face × ear × shelf (2×2×3 = 12 dans ce test).
    expect(res.data.data.total_capacity).toBe(12);
    expect(res.data.data).toHaveProperty('occupied_spots');
    expect(res.data.data).toHaveProperty('available_spots');
    expect(res.data.data).toHaveProperty('occupancy_percentage');
    expect(res.data.data).toHaveProperty('volumetry_ml');
  });

  it('destroy : supprime la chaîne et répond 204', { timeout: 30000 }, async () => {
    const container = created.find((c) => c.type === 'container');
    const shelf = created.find((c) => c.type === 'shelf');
    const room = created.find((c) => c.type === 'room');

    const containerRes = await del(`/containers/${container.id}`, { token });
    expect(containerRes.status).toBe(204);

    const shelfRes = await del(`/shelves/${shelf.id}`, { token });
    expect(shelfRes.status).toBe(204);

    const roomRes = await del(`/rooms/${room.id}`, { token });
    expect(roomRes.status).toBe(204);

    // Marqués supprimés : le cleanup ne les retentera pas.
    created.splice(created.indexOf(container), 1);
    created.splice(created.indexOf(shelf), 1);
    created.splice(created.indexOf(room), 1);
  });
});
