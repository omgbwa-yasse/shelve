/**
 * Client HTTP de la suite de conformité.
 *
 * RÈGLE ABSOLUE : ce fichier est le seul à connaître l'URL du backend, lue dans
 * API_BASE_URL. Aucun test ne doit contenir d'URL absolue, ni la moindre référence à
 * Laravel, PHP, Eloquent, Spring ou Java.
 *
 * C'est ce qui permettra, en phase 3, de rejouer la suite entière contre Spring Boot
 * en changeant une variable d'environnement — et de traiter toute adaptation d'un test
 * comme l'aveu d'une divergence, et non comme un ajustement anodin.
 */

const BASE_URL = (process.env.API_BASE_URL || 'http://localhost:8000/api/v1').replace(/\/$/, '');

export const baseUrl = BASE_URL;

/**
 * Exécute une requête et renvoie une réponse normalisée, exploitable telle quelle
 * dans les assertions.
 */
export async function request(method, path, { token, body, headers = {}, raw = false } = {}) {
  const url = `${BASE_URL}${path.startsWith('/') ? path : `/${path}`}`;

  const finalHeaders = {
    Accept: 'application/json',
    'Accept-Language': 'fr',
    ...headers,
  };

  if (token) finalHeaders.Authorization = `Bearer ${token}`;

  let payload;
  if (body !== undefined) {
    if (body instanceof FormData) {
      payload = body; // le navigateur/undici pose lui-même le Content-Type + boundary
    } else {
      finalHeaders['Content-Type'] = 'application/json';
      payload = JSON.stringify(body);
    }
  }

  const response = await fetch(url, { method, headers: finalHeaders, body: payload });

  const contentType = response.headers.get('content-type') || '';
  let data = null;

  if (raw) {
    data = Buffer.from(await response.arrayBuffer());
  } else if (response.status === 204) {
    // 204 = pas de contenu, par définition. Certains serveurs n'envoient alors aucun
    // Content-Type : sans ce cas explicite, on lirait une chaîne vide et un backend
    // paraîtrait diverger d'un autre sur du néant.
    data = null;
  } else if (contentType.includes('json')) {
    // Un corps vide ferait échouer response.json().
    const text = await response.text();
    data = text ? JSON.parse(text) : null;
  } else {
    const text = await response.text();
    data = text === '' ? null : text;
  }

  return {
    status: response.status,
    headers: Object.fromEntries(response.headers.entries()),
    contentType,
    data,
  };
}

export const get = (path, opts) => request('GET', path, opts);
export const post = (path, opts) => request('POST', path, opts);
export const put = (path, opts) => request('PUT', path, opts);
export const patch = (path, opts) => request('PATCH', path, opts);
export const del = (path, opts) => request('DELETE', path, opts);

/**
 * Authentifie un compte et renvoie son token.
 *
 * Les identifiants viennent de l'environnement : la suite ne doit contenir aucun
 * secret, et doit pouvoir viser un jeu de données différent selon l'environnement.
 */
const tokenCache = new Map();

/**
 * Variante mise en cache : un seul login par compte et par exécution.
 *
 * Sans cela, chaque suite refait une authentification et sature le quota `auth`
 * (5 tentatives/heure) — le harnais se bloquerait lui-même.
 *
 * À ne pas utiliser dans un test qui vérifie la révocation d'un token : il lui faut
 * un token frais, donc `login()`.
 */
export async function cachedLogin(
  email = process.env.API_TEST_EMAIL,
  password = process.env.API_TEST_PASSWORD,
) {
  if (!tokenCache.has(email)) {
    tokenCache.set(email, await login(email, password));
  }

  return tokenCache.get(email);
}

export async function login(
  email = process.env.API_TEST_EMAIL,
  password = process.env.API_TEST_PASSWORD,
) {
  if (!email || !password) {
    throw new Error(
      'Identifiants absents : définir API_TEST_EMAIL et API_TEST_PASSWORD ' +
        '(voir contracts/conformance/README.md).',
    );
  }

  const res = await post('/auth/login', {
    body: { email, password, device_name: 'conformance' },
  });

  if (res.status !== 200) {
    throw new Error(`Échec d'authentification (${res.status}) : ${JSON.stringify(res.data)}`);
  }

  return res.data.data.token;
}

/**
 * Vérifie qu'un backend répond ET qu'il s'agit bien de l'API attendue.
 *
 * Le simple fait d'obtenir une réponse ne suffit pas : un autre service peut occuper
 * le port (constaté — un serveur Django répondait sur le 8000). La suite dialoguerait
 * alors avec le mauvais backend et produirait des échecs incompréhensibles. Pire, en
 * phase 3, on pourrait croire tester Spring Boot tout en interrogeant Laravel.
 *
 * Signature retenue : `/auth/me` sans token doit répondre 401 en JSON.
 */
export async function assertBackendIsOurs() {
  let res;

  try {
    res = await fetch(`${BASE_URL}/auth/me`, { method: 'GET', headers: { Accept: 'application/json' } });
  } catch (cause) {
    throw new Error(`Backend injoignable sur ${BASE_URL} — est-il démarré ?`, { cause });
  }

  if (res.status !== 401) {
    throw new Error(
      `${BASE_URL}/auth/me a répondu ${res.status}, alors que 401 est attendu sans token.\n` +
        "Un autre service occupe probablement ce port : vérifier API_BASE_URL.",
    );
  }

  const type = res.headers.get('content-type') || '';
  if (!type.includes('json')) {
    throw new Error(
      `${BASE_URL} a répondu en "${type}" au lieu de JSON.\n` +
        "Ce n'est pas l'API attendue : vérifier API_BASE_URL.",
    );
  }
}
