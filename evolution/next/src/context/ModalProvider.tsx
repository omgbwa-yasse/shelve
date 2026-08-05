'use client';

import { createContext, useCallback, useContext, useMemo, useState } from 'react';

type ModalRenderer = (close: () => void) => React.ReactNode;

type ModalContextValue = {
  open: (renderer: ModalRenderer) => void;
  close: () => void;
};

const ModalContext = createContext<ModalContextValue | null>(null);

/**
 * Registre central des modales : `useModal().open(...)` permet d'ouvrir
 * n'importe quelle modale (confirmation, `SelectionModal`, formulaire) depuis
 * n'importe quel composant, sans que chaque écran ne gère son propre state
 * `isOpen` — un seul point de vérité, une seule modale visible à la fois.
 * `Modal`/`SelectionModal` restent utilisables en mode contrôlé (props
 * `open`/`onClose`) quand un écran a besoin de piloter finement le cycle de vie.
 */
export function ModalProvider({ children }: { children: React.ReactNode }) {
  const [renderer, setRenderer] = useState<ModalRenderer | null>(null);

  const close = useCallback(() => setRenderer(null), []);
  const open = useCallback((next: ModalRenderer) => setRenderer(() => next), []);

  const value = useMemo(() => ({ open, close }), [open, close]);

  return (
    <ModalContext.Provider value={value}>
      {children}
      {renderer?.(close)}
    </ModalContext.Provider>
  );
}

export function useModal(): ModalContextValue {
  const ctx = useContext(ModalContext);
  if (!ctx) throw new Error('useModal doit être utilisé sous <ModalProvider>');
  return ctx;
}
