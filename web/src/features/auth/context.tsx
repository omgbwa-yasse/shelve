'use client';

import { createContext, useContext, type ReactNode } from 'react';

type SessionContextValue = {
  permissions: string[];
};

const SessionContext = createContext<SessionContextValue>({ permissions: [] });

/**
 * Rend les permissions effectives de l'agent connecté (`SessionUser.permissions`,
 * lues côté serveur par `getSession()`) accessibles aux composants client — voir
 * `lib/permissions.ts::can()`. Sert notamment à borner l'affichage du panneau
 * assistant IA aux actions réellement autorisées (exigence du 2026-08-05) : le
 * contrôle réel reste les policies Laravel, ceci n'est qu'un miroir d'ergonomie.
 */
export function SessionProvider({ permissions, children }: { permissions: string[]; children: ReactNode }) {
  return <SessionContext.Provider value={{ permissions }}>{children}</SessionContext.Provider>;
}

export function useSessionPermissions(): string[] {
  return useContext(SessionContext).permissions;
}
