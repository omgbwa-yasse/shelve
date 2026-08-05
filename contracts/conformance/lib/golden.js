/**
 * Golden files — la mémoire de ce que l'API répond aujourd'hui.
 *
 * Chaque réponse normalisée est enregistrée sous `golden/`. En phase 1, ces fichiers
 * fixent le comportement de Laravel. En phase 3, la même suite tourne contre Spring
 * Boot et compare aux MÊMES fichiers : toute différence est une divergence à traiter.
 *
 * Un golden file modifié doit être justifié en revue. C'est le garde-fou contre la
 * régression silencieuse : sans lui, « le test passe » signifie seulement « le test a
 * été ajusté ».
 */

import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { expect } from 'vitest';
import { normalize } from './normalize.js';

const HERE = dirname(fileURLToPath(import.meta.url));
const GOLDEN_DIR = join(HERE, '..', 'golden');

const UPDATE = process.env.UPDATE_GOLDEN === '1';

/**
 * Compare une réponse à son golden file, ou le crée s'il n'existe pas encore.
 *
 * @param {string} name  identifiant stable, ex. "D01/activities/index"
 * @param {object} value réponse à figer (statut + corps)
 */
export function matchGolden(name, value) {
  const file = join(GOLDEN_DIR, `${name}.json`);
  const normalized = normalize(value);
  const serialized = `${JSON.stringify(normalized, null, 2)}\n`;

  if (!existsSync(file) || UPDATE) {
    mkdirSync(dirname(file), { recursive: true });
    writeFileSync(file, serialized, 'utf8');

    if (!UPDATE) {
      console.info(`  golden créé : ${name}`);
    }
    return;
  }

  const expected = JSON.parse(readFileSync(file, 'utf8'));

  expect(
    normalized,
    `La réponse diverge du golden file "${name}".\n` +
      "Si l'écart est voulu : UPDATE_GOLDEN=1 npm test — et le justifier en revue.\n" +
      'Si la suite tourne contre un nouveau backend : cet écart EST la divergence à corriger.',
  ).toEqual(expected);
}

/** Réponse réduite à ce qui doit être identique entre deux implémentations. */
export function comparable(response) {
  return {
    status: response.status,
    body: response.data,
  };
}
