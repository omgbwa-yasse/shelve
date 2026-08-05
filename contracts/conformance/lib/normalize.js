/**
 * Normalisation des réponses avant comparaison.
 *
 * Deux implémentations correctes de la même API ne produisent pas des octets
 * identiques : les identifiants auto-générés diffèrent, les horodatages aussi, et
 * l'ordre des clés d'un objet JSON n'a aucune signification. Comparer brut ferait
 * échouer Spring Boot sur du bruit et masquerait les vraies divergences.
 *
 * Ce qui est neutralisé :
 *   - identifiants auto-générés        → "<id>"
 *   - horodatages                      → "<timestamp>"
 *   - jetons, URLs absolues            → "<token>", "<url>"
 *   - ordre des clés d'objet           → trié
 *
 * Ce qui NE l'est PAS, et doit rester comparé strictement :
 *   - la présence et le nom des champs
 *   - les types
 *   - les valeurs métier
 *   - l'ORDRE DES COLLECTIONS : il est significatif (tri demandé, hiérarchie).
 *     Le neutraliser laisserait passer un ORDER BY divergent — précisément le
 *     genre d'écart que la collation MySQL vs Java peut produire (risque R14).
 */

const ISO_DATETIME = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:?\d{2})$/;
const SQL_DATETIME = /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/;

/** Champs dont la valeur varie d'une exécution à l'autre sans porter de sens. */
const VOLATILE_KEYS = new Set([
  'id',
  'token',
  'created_at',
  'updated_at',
  'deleted_at',
  'email_verified_at',
  'last_used_at',
  'expires_at',
]);

/** Champs dont le NOM se termine par _id : identifiants de relation. */
const ID_SUFFIX = /_id$/;

export function normalize(value, options = {}) {
  const { keepIds = false } = options;
  return walk(value, keepIds);
}

function walk(value, keepIds) {
  if (value === null || value === undefined) return null;

  if (Array.isArray(value)) {
    // Ordre PRÉSERVÉ — voir l'en-tête.
    return value.map((v) => walk(v, keepIds));
  }

  if (typeof value === 'object') {
    const out = {};
    // Tri des clés : l'ordre d'un objet JSON n'est pas significatif, et deux
    // sérialiseurs (Laravel Resource, Jackson) ne le produisent pas pareil.
    for (const key of Object.keys(value).sort()) {
      out[key] = normalizeField(key, value[key], keepIds);
    }
    return out;
  }

  if (typeof value === 'string') {
    if (ISO_DATETIME.test(value) || SQL_DATETIME.test(value)) return '<timestamp>';
    if (/^https?:\/\//.test(value)) return '<url>';
    if (/^\d+\|[A-Za-z0-9]{20,}$/.test(value)) return '<token>'; // token porteur
  }

  return value;
}

function normalizeField(key, value, keepIds) {
  if (!keepIds && (VOLATILE_KEYS.has(key) || ID_SUFFIX.test(key))) {
    if (value === null) return null; // null porte du sens : la relation est absente
    if (key === 'token') return '<token>';
    if (key.endsWith('_at')) return '<timestamp>';
    return '<id>';
  }

  return walk(value, keepIds);
}

/**
 * Extrait la forme d'une réponse : quels champs, de quel type.
 *
 * C'est l'assertion la plus utile pour comparer deux implémentations : elle attrape
 * un champ manquant, ajouté, ou dont le type a glissé (un booléen devenu 0/1, un
 * décimal devenu flottant) — sans dépendre des données du jeu d'essai.
 */
export function shapeOf(value) {
  if (value === null || value === undefined) return 'null';
  if (Array.isArray(value)) {
    return value.length === 0 ? 'array<empty>' : `array<${shapeOf(value[0])}>`;
  }
  if (typeof value === 'object') {
    const out = {};
    for (const key of Object.keys(value).sort()) out[key] = shapeOf(value[key]);
    return out;
  }
  return typeof value;
}
