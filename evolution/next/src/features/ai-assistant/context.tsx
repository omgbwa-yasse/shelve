'use client';

import { createContext, useContext, useMemo, useState, type ReactNode } from 'react';

type AiAssistantContextValue = {
  isOpen: boolean;
  toggle: () => void;
  open: () => void;
  close: () => void;
  /** Conversation actuellement affichée dans l'onglet Chat (null = nouvelle conversation). */
  activeConversationId: string | null;
  openConversation: (id: string | null) => void;
};

const AiAssistantContext = createContext<AiAssistantContextValue | null>(null);

/**
 * Fournit l'état d'ouverture du panneau IA (icône Topbar) et la conversation
 * active — partagé entre le bouton de la Topbar et le panneau lui-même, tous
 * deux montés dans `(back-office)/layout.tsx` sans lien parent/enfant direct.
 */
export function AiAssistantProvider({ children }: { children: ReactNode }) {
  const [isOpen, setIsOpen] = useState(false);
  const [activeConversationId, setActiveConversationId] = useState<string | null>(null);

  const value = useMemo<AiAssistantContextValue>(
    () => ({
      isOpen,
      toggle: () => setIsOpen((v) => !v),
      open: () => setIsOpen(true),
      close: () => setIsOpen(false),
      activeConversationId,
      openConversation: (id) => {
        setActiveConversationId(id);
        setIsOpen(true);
      },
    }),
    [isOpen, activeConversationId],
  );

  return <AiAssistantContext.Provider value={value}>{children}</AiAssistantContext.Provider>;
}

export function useAiAssistant(): AiAssistantContextValue {
  const ctx = useContext(AiAssistantContext);
  if (!ctx) throw new Error('useAiAssistant doit être utilisé sous AiAssistantProvider');
  return ctx;
}
