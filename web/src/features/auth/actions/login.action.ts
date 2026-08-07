'use server';

import { cookies } from 'next/headers';
import { redirect } from 'next/navigation';
import { loginSchema } from '../utils/auth.schema';
import { getBackendUrl } from '@/lib/api/client';
import type { LoginResult, SessionUser } from '../types/auth.types';

const TOKEN_COOKIE = 'shelve_token';

export type LoginActionState = { message?: string; error?: string };

/**
 * Server Action de connexion — le token Sanctum est stocké dans un cookie
 * httpOnly côté serveur ; il ne touche jamais le JS client.
 */
export async function loginAction(
  _prev: LoginActionState,
  formData: FormData,
): Promise<LoginActionState> {
  const parsed = loginSchema.safeParse({
    email: formData.get('email'),
    password: formData.get('password'),
    device_name: 'next-web',
  });

  if (!parsed.success) {
    return { error: parsed.error.issues[0]?.message ?? 'Identifiants invalides.' };
  }

  const backendUrl = getBackendUrl();
  if (!backendUrl) return { error: 'Backend non configuré.' };

  try {
    const upstream = await fetch(`${backendUrl}/api/v1/auth/login`, {
      method: 'POST',
      headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify(parsed.data),
      cache: 'no-store',
    });

    const json = (await upstream.json().catch(() => null)) as
      | { data?: { token?: string; user?: SessionUser }; message?: string }
      | null;

    if (!upstream.ok) {
      return { error: json?.message ?? 'Connexion refusée.' };
    }

    const token = json?.data?.token;
    if (!token) return { error: 'Réponse du serveur invalide.' };

    const cookieStore = await cookies();
    cookieStore.set(TOKEN_COOKIE, token, {
      httpOnly: true,
      sameSite: 'lax',
      secure: process.env.NODE_ENV === 'production',
      path: '/',
      maxAge: 60 * 60 * 24 * 7,
    });
  } catch {
    return { error: 'Impossible de contacter le serveur.' };
  }

  // Connexion réussie → atterrissage sur le back-office (liste des notices).
  redirect('/records');
}

/**
 * Server Action de déconnexion : appelle Laravel puis efface le cookie.
 */
export async function logoutAction(): Promise<void> {
  const backendUrl = getBackendUrl();
  const cookieStore = await cookies();
  const token = cookieStore.get(TOKEN_COOKIE)?.value;

  if (backendUrl && token) {
    await fetch(`${backendUrl}/api/v1/auth/logout`, {
      method: 'POST',
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      cache: 'no-store',
    }).catch(() => null);
  }

  cookieStore.delete(TOKEN_COOKIE);
  redirect('/login');
}

export type { LoginResult };
