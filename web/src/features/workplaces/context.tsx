'use client';

import { createContext, useContext } from 'react';
import { useQuery } from '@tanstack/react-query';
import { getWorkplace } from './services/workplace.service';
import type { Workplace } from './types';

type WorkplaceContextValue = {
  /** Code (slug) du workplace courant, lu dans l'URL. */
  code: string;
  workplace: Workplace | null;
  isLoading: boolean;
  isError: boolean;
  refetch: () => void;
};

const WorkplaceContext = createContext<WorkplaceContextValue | null>(null);

/**
 * Fournit le workplace courant (résolu par code) à toutes les pages du layout
 * `/workplace/{code}`. Le layout d'accueil (`WorkplaceShell`) consomme la même
 * donnée pour dessiner la bannière, sans requête en double.
 */
export function WorkplaceProvider({ code, children }: { code: string; children: React.ReactNode }) {
  const { data, isLoading, isError, refetch } = useQuery({
    queryKey: ['workplace', code],
    queryFn: () => getWorkplace(code),
    enabled: code.length > 0,
  });

  const value: WorkplaceContextValue = {
    code,
    workplace: data?.data ?? null,
    isLoading,
    isError,
    refetch: () => void refetch(),
  };

  return <WorkplaceContext.Provider value={value}>{children}</WorkplaceContext.Provider>;
}

export function useWorkplace(): WorkplaceContextValue {
  const ctx = useContext(WorkplaceContext);
  if (!ctx) {
    throw new Error('useWorkplace doit être utilisé dans un WorkplaceProvider.');
  }
  return ctx;
}
