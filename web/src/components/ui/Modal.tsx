'use client';

import { useEffect } from 'react';
import { createPortal } from 'react-dom';
import clsx from 'clsx';
import { Icon } from '@/components/icons';

export type ModalSize = 'sm' | 'md' | 'lg' | 'full';

/** Nombre de colonnes de la grille de contenu — voir prop `columns`. */
export type ModalColumns = 1 | 2 | 3 | 4;

export type ModalProps = {
  open: boolean;
  onClose: () => void;
  title: string;
  size?: ModalSize;
  children: React.ReactNode;
  footer?: React.ReactNode;
  /**
   * Champ de recherche affiché dans l'en-tête, entre le titre et la croix de
   * fermeture. `onChange` reçoit la saisie à chaque frappe ; à l'appelant de
   * décider du seuil de déclenchement (ex. lancer le filtre à partir de 3
   * caractères) — `Modal` se contente d'afficher le champ contrôlé.
   */
  search?: { value: string; onChange: (value: string) => void; placeholder?: string };
  /**
   * Affiche le contenu en grille de N colonnes (responsive : 1 colonne en
   * dessous de `sm`, N à partir de `sm`) plutôt qu'en flux vertical — utile
   * pour une liste de choix/cartes (typologies, gabarits, icônes…). Omis =
   * comportement historique (le contenu gère seul sa mise en page).
   */
  columns?: ModalColumns;
};

const COLUMNS_CLASSES: Record<ModalColumns, string> = {
  1: 'grid-cols-1',
  2: 'grid-cols-1 sm:grid-cols-2',
  3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
  4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4',
};

const SIZE_CLASSES: Record<ModalSize, string> = {
  sm: 'max-w-md',
  md: 'max-w-2xl',
  lg: 'max-w-4xl',
  // 'full' couvre la page : c'est le format utilisé par `SelectionModal` pour
  // parcourir/sélectionner dans un grand volume de données (voir PHILOSOPHY.md).
  // Même marge fixe de 25px que l'overlay (MODAL_MARGIN_PX) sur les 4 côtés.
  full: 'inset-[25px] max-w-none',
};

/** Marge fixe (haut/bas/gauche/droite) entre la modale et le bord du viewport. */
const MODAL_MARGIN_PX = 25;

/**
 * Primitive UNIQUE de modale de l'application. Tout affichage de contenu
 * par-dessus la page (confirmation, formulaire, sélecteur de gros volume)
 * passe par ce composant — jamais par une modale ad hoc écran par écran.
 * Toujours une croix de fermeture en haut à droite, fermeture au clavier
 * (Échap) et au clic sur l'overlay.
 */
export function Modal({ open, onClose, title, size = 'md', children, footer, columns, search }: ModalProps) {
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
    <div
      className="fixed inset-0 z-50 flex items-center justify-center"
      style={{ padding: `${MODAL_MARGIN_PX}px` }}
    >
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
          // Marge fixe de 30px garantie sur les 4 côtés : `full` la porte via
          // `inset-[30px]` (positionnement absolu) ; les autres tailles héritent
          // du padding de l'overlay ci-dessus + ce plafond de hauteur.
          // Valeur statique (Tailwind JIT n'évalue pas les templates interpolés) —
          // garder en phase avec MODAL_MARGIN_PX (25px) si celui-ci change.
          isFull ? 'absolute' : 'w-full max-h-[calc(100vh-50px)]',
          SIZE_CLASSES[size],
        )}
      >
        <div className="flex shrink-0 items-center justify-between gap-3 border-b border-border px-4 py-3">
          <h2 className="shrink-0 text-base font-semibold">{title}</h2>
          <div className="flex flex-1 items-center justify-end gap-3">
            {search && (
              <input
                type="search"
                value={search.value}
                onChange={(e) => search.onChange(e.target.value)}
                placeholder={search.placeholder ?? 'Rechercher…'}
                className="w-full max-w-xs rounded border border-border bg-background px-3 py-1.5 text-sm"
              />
            )}
            <button
              type="button"
              onClick={onClose}
              aria-label="Fermer"
              className="shrink-0 rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
            >
              <Icon name="close" className="h-5 w-5" />
            </button>
          </div>
        </div>

        <div className="min-h-0 flex-1 overflow-y-auto p-4">
          {columns ? <div className={clsx('grid gap-3', COLUMNS_CLASSES[columns])}>{children}</div> : children}
        </div>

        {footer && <div className="shrink-0 border-t border-border px-4 py-3">{footer}</div>}
      </div>
    </div>,
    document.body,
  );
}
