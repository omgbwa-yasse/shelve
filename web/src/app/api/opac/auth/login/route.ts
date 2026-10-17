import { NextRequest, NextResponse } from 'next/server';
import { getBackendUrl } from '@/lib/api/client';

export const dynamic = 'force-dynamic';

const PUBLIC_TOKEN_COOKIE = 'shelve_public_token';

/**
 * Connexion portail public : appelle Laravel `/api/public/users/login` côté
 * serveur, place le token Sanctum dans un cookie httpOnly dédié
 * (`shelve_public_token`), puis renvoie l'utilisateur. Le token n'est jamais
 * exposé au JS client. Cookie distinct de `shelve_token` (agents) : le proxy
 * utilise l'un ou l'autre selon le contexte.
 */
export async function POST(request: NextRequest) {
  const backendUrl = getBackendUrl();
  if (!backendUrl) {
    return NextResponse.json({ message: 'Backend non configuré' }, { status: 500 });
  }

  const payload = await request.json().catch(() => ({}));

  const upstream = await fetch(`${backendUrl}/api/public/users/login`, {
    method: 'POST',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
    cache: 'no-store',
  });

  const json = await upstream.json().catch(() => null);

  if (!upstream.ok) {
    return NextResponse.json(json ?? { message: 'Connexion refusée' }, { status: upstream.status });
  }

  const token = json?.data?.token;
  const user = json?.data?.user;

  const response = NextResponse.json({ data: { user } });

  if (token) {
    response.cookies.set(PUBLIC_TOKEN_COOKIE, String(token), {
      httpOnly: true,
      sameSite: 'lax',
      secure: process.env.NODE_ENV === 'production',
      path: '/',
      maxAge: 60 * 60 * 24 * 7,
    });
  }

  return response;
}
