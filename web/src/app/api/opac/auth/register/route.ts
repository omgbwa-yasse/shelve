import { NextRequest, NextResponse } from 'next/server';
import { getBackendUrl } from '@/lib/api/client';

export const dynamic = 'force-dynamic';

/**
 * Inscription portail public : appelle Laravel `/api/public/users/register`.
 * Aucun cookie posé (compte en attente d'approbation).
 */
export async function POST(request: NextRequest) {
  const backendUrl = getBackendUrl();
  if (!backendUrl) {
    return NextResponse.json({ message: 'Backend non configuré' }, { status: 500 });
  }

  const payload = await request.json().catch(() => ({}));

  const upstream = await fetch(`${backendUrl}/api/public/users/register`, {
    method: 'POST',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
    cache: 'no-store',
  });

  const json = await upstream.json().catch(() => null);

  return NextResponse.json(json ?? { message: 'Inscription refusée' }, { status: upstream.status });
}
