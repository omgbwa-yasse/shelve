import { NextRequest, NextResponse } from 'next/server';

export const dynamic = 'force-dynamic';

const PUBLIC_TOKEN_COOKIE = 'shelve_public_token';

/**
 * Déconnexion portail public : supprime le cookie `shelve_public_token`.
 * (La révocation du token Sanctum côté Laravel est hors périmètre : la route
 * `/api/public/users/logout` n'est pas exposée dans routes/api.php.)
 */
export async function POST(request: NextRequest) {
  const response = NextResponse.json({ success: true });
  response.cookies.set(PUBLIC_TOKEN_COOKIE, '', {
    httpOnly: true,
    sameSite: 'lax',
    secure: process.env.NODE_ENV === 'production',
    path: '/',
    maxAge: 0,
  });

  return response;
}
