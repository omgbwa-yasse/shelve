import { NextRequest, NextResponse } from 'next/server';
import { getBackendUrl } from '@/lib/api/client';

/**
 * Proxy API — unique point d'entrée navigateur vers le backend Laravel.
 * Lit le cookie httpOnly `shelve_token` et le renvoie en en-tête
 * `Authorization: Bearer` au backend. Le token ne touche jamais le JS client.
 */
export const dynamic = 'force-dynamic';

const TOKEN_COOKIE = 'shelve_token';

async function proxy(request: NextRequest, ctx: { params: Promise<{ path: string[] }> }) {
  const { path } = await ctx.params;
  const backendUrl = getBackendUrl();

  if (!backendUrl) {
    return NextResponse.json({ message: 'NEXT_PUBLIC_API_BASE_URL non configuré' }, { status: 500 });
  }

  const target = `${backendUrl}/${path.join('/')}${request.nextUrl.search}`;
  const token = request.cookies.get(TOKEN_COOKIE)?.value;

  // Préserve le Content-Type entrant (application/json pour les API, ou
  // multipart/form-data avec boundary pour les téléversements de fichier).
  const incomingContentType = request.headers.get('content-type');

  const headers: Record<string, string> = {
    Accept: 'application/json',
    'Content-Type': incomingContentType ?? 'application/json',
    ...(token ? { Authorization: `Bearer ${token}` } : {}),
  };
  // On propage l'en-tête d'origine si présent (ex. X-XSRF-TOKEN en état).
  const xsrf = request.headers.get('x-xsrf-token');
  if (xsrf) headers['X-XSRF-TOKEN'] = xsrf;

  const body = ['GET', 'HEAD'].includes(request.method) ? undefined : await request.arrayBuffer();

  try {
    const upstream = await fetch(target, {
      method: request.method,
      headers,
      body: body?.byteLength ? body : undefined,
      cache: 'no-store',
    });

    const contentType = upstream.headers.get('content-type') ?? 'application/json';
    const upstreamBody = await upstream.arrayBuffer();

    const response = new NextResponse(upstreamBody, {
      status: upstream.status,
      headers: {
        'Content-Type': contentType,
        'Cache-Control': 'no-store',
      },
    });

    // Transmet le Content-Disposition (téléchargement de fichier, ex. sandbox).
    const disposition = upstream.headers.get('content-disposition');
    if (disposition) {
      response.headers.set('Content-Disposition', disposition);
    }

    // Retransmet les cookies émis par Laravel (ex. XSRF-TOKEN) vers le client.
    const setCookie = upstream.headers.get('set-cookie');
    if (setCookie) {
      response.headers.set('Set-Cookie', setCookie);
    }

    return response;
  } catch {
    return NextResponse.json({ message: 'Backend injoignable' }, { status: 502 });
  }
}

export const GET = proxy;
export const POST = proxy;
export const PUT = proxy;
export const PATCH = proxy;
export const DELETE = proxy;
