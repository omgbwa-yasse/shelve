import { NextRequest, NextResponse } from 'next/server';
import { getBackendUrl } from '@/lib/api/client';

export const dynamic = 'force-dynamic';

const TOKEN_COOKIE = 'shelve_token';

/**
 * Connexion : le Route Handler appelle Laravel `/api/v1/auth/login` côté
 * serveur, place le token Sanctum dans un cookie httpOnly, puis renvoie
 * l'utilisateur. Le token n'est jamais exposé au JS client.
 */
export async function POST(request: NextRequest) {
  const backendUrl = getBackendUrl();
  if (!backendUrl) {
    return NextResponse.json({ message: 'Backend non configuré' }, { status: 500 });
  }

  const payload = await request.json().catch(() => ({}));

  const upstream = await fetch(`${backendUrl}/api/v1/auth/login`, {
    method: 'POST',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
    cache: 'no-store',
  });

  const json = await upstream.json().catch(() => null);

  if (!upstream.ok) {
    return NextResponse.json(
      json ?? { message: 'Connexion refusée' },
      { status: upstream.status },
    );
  }

  const token = json?.data?.token;
  const user = json?.data?.user;

  const response = NextResponse.json({ data: { user } });

  if (token) {
    response.cookies.set(TOKEN_COOKIE, String(token), {
      httpOnly: true,
      sameSite: 'lax',
      secure: process.env.NODE_ENV === 'production',
      path: '/',
      maxAge: 60 * 60 * 24 * 7,
    });
  }

  return response;
}
