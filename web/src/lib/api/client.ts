/**
 * SEUL fichier du projet qui a le droit de lire `NEXT_PUBLIC_API_BASE_URL`
 * (voir PHASE-2-NEXTJS.md, "Règle d'architecture non négociable").
 *
 * Le client navigateur n'appelle JAMAIS le backend directement : il passe par
 * le proxy Next (`/api/proxy/*`) qui porte le token Sanctum en cookie httpOnly
 * côté serveur. `getBackendUrl()` est importé par les Route Handlers du proxy.
 */

const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL;

if (!API_BASE_URL && typeof window === 'undefined') {
  // eslint-disable-next-line no-console
  console.warn('[api/client] NEXT_PUBLIC_API_BASE_URL est absent — voir .env.local.example');
}

/** URL du backend — consommée uniquement par le proxy serveur. */
export function getBackendUrl(): string {
  return API_BASE_URL ?? '';
}

export type ApiRequestOptions = Omit<RequestInit, 'body'> & {
  body?: unknown;
};

/**
 * Enveloppe fine autour de `fetch`, par le proxy Next (même origine).
 * Remplacer par `openapi-fetch` typé une fois `schema.d.ts` généré.
 */
export async function apiFetch<T>(path: string, options: ApiRequestOptions = {}): Promise<T> {
  const response = await fetch(`/api/proxy${path}`, {
    ...options,
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...options.headers,
    },
    body: options.body ? JSON.stringify(options.body) : undefined,
  });

  if (!response.ok) {
    throw new ApiError(response.status, await safeJson(response));
  }

  return safeJson(response) as Promise<T>;
}

export class ApiError extends Error {
  constructor(
    public status: number,
    public payload: unknown,
  ) {
    super(`API error ${status}`);
  }
}

async function safeJson(response: Response): Promise<unknown> {
  try {
    return await response.json();
  } catch {
    return null;
  }
}
