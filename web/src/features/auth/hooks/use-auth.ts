'use client';

import { useCallback, useState } from 'react';
import type { SessionUser } from '../types/auth.types';

/**
 * Hook d'authentification côté client : état de session + actions login/logout.
 * Les appels passent par les Server Actions / Route Handlers (token httpOnly).
 */
export function useAuth(initial?: SessionUser | null) {
  const [user, setUser] = useState<SessionUser | null>(initial ?? null);
  const [pending, setPending] = useState(false);

  const login = useCallback(async (email: string, password: string): Promise<string | null> => {
    setPending(true);
    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password, device_name: 'next-web' }),
      });
      const json = (await res.json().catch(() => null)) as { data?: { user?: SessionUser }; message?: string } | null;

      if (!res.ok) {
        return json?.message ?? 'Identifiants invalides.';
      }
      setUser(json?.data?.user ?? null);
      return null;
    } catch {
      return 'Impossible de contacter le serveur.';
    } finally {
      setPending(false);
    }
  }, []);

  const logout = useCallback(async () => {
    setPending(true);
    try {
      await fetch('/api/auth/logout', { method: 'POST' });
      setUser(null);
      window.location.href = '/login';
    } finally {
      setPending(false);
    }
  }, []);

  return { user, setUser, login, logout, pending };
}
