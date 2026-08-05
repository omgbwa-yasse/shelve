import { getBackendUrl } from '@/lib/api/client';
import type { SessionUser } from '../types/auth.types';

const TOKEN_COOKIE = 'shelve_token';

/**
 * Session agent — lue côté serveur (Server Component / Server Action) depuis
 * le cookie httpOnly, en interrogeant Laravel `GET /api/v1/auth/me`.
 */
export async function getSession(): Promise<SessionUser | null> {
  const { cookies } = await import('next/headers');
  const cookieStore = await cookies();
  const token = cookieStore.get(TOKEN_COOKIE)?.value;

  if (!token) return null;

  const backendUrl = getBackendUrl();
  if (!backendUrl) return null;

  try {
    const upstream = await fetch(`${backendUrl}/api/v1/auth/me`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      cache: 'no-store',
    });

    if (!upstream.ok) return null;

    const json = (await upstream.json()) as { data?: { user?: SessionUser } };
    return json.data?.user ?? null;
  } catch {
    return null;
  }
}
