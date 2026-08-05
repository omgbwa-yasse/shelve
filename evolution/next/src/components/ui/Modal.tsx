'use client';

import { useEffect } from 'react';
import { createPortal } from 'react-dom';
import clsx from 'clsx';
import { Icon } from '@/components/icons';

export type ModalSize = 'sm' | 'md' | 'lg' | 'full';

export type ModalProps = {
  open: boolean;
  onClose: () => void;
  title: string;
  size?: ModalSize;
  children: React.ReactNode;
  footer?: React.ReactNode;
};

const SIZE_CLASSES: Record<ModalSize, string> = {
  sm: 'max-w-md',
  md: 'max-w-2xl',
  lg: 'max-w-4xl',
  // 'full' couvre la page : c'est le format utilisé par `SelectionModal` pour
  // parcourir/sélectionner dans un grand volume de données (voir PHILOSOPHY.md).
  full: 'inset-4 max-w-none',
};

/**
 * Primitive UNIQUE de modale de l'application. Tout affichage de contenu
 * par-dessus la page (confirmation, formulaire, sélecteur de gros volume)
 * passe par ce composant — jamais par une modale ad hoc écran par écran.
 * Toujours une croix de fermeture en haut à droite, fermeture au clavier
 * (Échap) et au clic sur l'overlay.
 */
export function Modal({ open, onClose, title, size = 'md', children, footer }: ModalProps) {
  useEffect(() => {
    if (!open) return;

    function onKeyDown(event: KeyboardEvent) {
      if (event.key === 'Escape') onClose();
    }

    document.addEventListener('keydown', onKeyDown);
    return () => document.removeEventListener('keydown', onKeyDown);
  }, [open, onClose]);

  if (!open || typeof document === 'undefined') return null;

  const isFull = size === 'full';

  return createPortal(
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-foreground/40" onClick={onClose} aria-hidden="true" />

      <div
        role="dialog"
        aria-modal="true"
        aria-label={title}
        className={clsx(
          // flex-col + min-h-0 : sans min-h-0, un enfant flex avec overflow-y-auto
          // grandit à la taille de son contenu au lieu de s'y limiter — c'est ce qui
          // faisait "exploser" la modale plein écran hors de ses limites.
          'relative flex min-h-0 flex-col overflow-hidden rounded-lg border border-border bg-background shadow-xl',
          isFull ? 'absolute' : 'max-h-[85vh] w-full',
          SIZE_CLASSES[size],
        )}
      >
        <div className="flex shrink-0 items-center justify-between border-b border-border px-4 py-3">
          <h2 className="text-base font-semibold">{title}</h2>
          <button
            type="button"
            onClick={onClose}
            aria-label="Fermer"
            className="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
          >
            <Icon name="close" className="h-5 w-5" />
          </button>
        </div>

        <div className="min-h-0 flex-1 overflow-y-auto p-4">{children}</div>

        {footer && <div className="shrink-0 border-t border-border px-4 py-3">{footer}</div>}
      </div>
    </div>,
    document.body,
  );
}
