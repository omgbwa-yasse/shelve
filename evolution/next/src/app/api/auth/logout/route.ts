import { NextRequest, NextResponse } from 'next/server';
import { getBackendUrl } from '@/lib/api/client';

export const dynamic = 'force-dynamic';

const TOKEN_COOKIE = 'shelve_token';

/**
 * Déconnexion : appelle Laravel `/api/v1/auth/logout` puis efface le cookie.
 */
export async function POST(request: NextRequest) {
  const backendUrl = getBackendUrl();
  const token = request.cookies.get(TOKEN_COOKIE)?.value;

  if (backendUrl && token) {
    await fetch(`${backendUrl}/api/v1/auth/logout`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      cache: 'no-store',
    }).catch(() => null);
  }

  const response = NextResponse.json({ ok: true });
  response.cookies.set(TOKEN_COOKIE, '', { httpOnly: true, path: '/', maxAge: 0 });
  return response;
}
