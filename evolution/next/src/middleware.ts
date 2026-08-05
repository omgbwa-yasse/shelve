import { NextRequest, NextResponse } from 'next/server';

/**
 * Middleware Next — protection du back-office + redirections.
 *
 * Complète le guard de session des layouts (défense en profondeur) : toute
 * route hors périmètre public sans cookie `shelve_token` est redirigée vers
 * `/login`. Le périmètre public couvre le portail OPAC (`/opac`), la racine,
 * l'authentification et le proxy API.
 */
const TOKEN_COOKIE = 'shelve_token';
const PUBLIC_PREFIXES = ['/login', '/opac', '/api'];

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Racine : redirigée vers /opac par app/page.tsx — non protégée.
  if (pathname === '/') {
    return NextResponse.next();
  }

  const isPublic = PUBLIC_PREFIXES.some(
    (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`),
  );

  if (isPublic) {
    return NextResponse.next();
  }

  if (!request.cookies.has(TOKEN_COOKIE)) {
    const login = new URL('/login', request.url);
    return NextResponse.redirect(login);
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/((?!_next/static|_next/image|favicon.ico).*)'],
};
