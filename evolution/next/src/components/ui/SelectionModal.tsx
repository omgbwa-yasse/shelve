'use client';

import { useState } from 'react';
import clsx from 'clsx';
import { Modal } from '@/components/ui/Modal';

export type SelectionColumn<T> = {
  key: string;
  label: string;
  render: (row: T) => React.ReactNode;
};

export type AlphabetFilter = {
  /** Lettre active (surlignée), ou `null`/`undefined` = "Tous". */
  activeLetter?: string | null;
  /** Appelé avec la lettre cliquée, ou `null` si on reclique la lettre active ("Tous"). */
  onSelectLetter: (letter: string | null) => void;
  /**
   * Restreint les lettres affichées à celles ayant au moins un résultat dans le
   * jeu courant (recherche/filtre appliqués) — omis = les 26 lettres affichées
   * en permanence (comportement historique de `SelectionModal`).
   */
  availableLetters?: Set<string>;
};

export type SelectionModalProps<T> = {
  open: boolean;
  onClose: () => void;
  title: string;
  columns: SelectionColumn<T>[];
  rows: T[];
  rowKey: (row: T) => string | number;
  multiple?: boolean;
  onSearch?: (query: string) => void;
  onConfirm: (selected: T[]) => void;
  /**
   * Nombre total de résultats dans le jeu de données (toutes pages/lettres
   * confondues) — sert à décider si l'index alphabétique et/ou la pagination
   * doivent apparaître (seuil : > 26 résultats). Par défaut, `rows.length`
   * (correct si le jeu tient sur une seule page).
   */
  totalCount?: number;
  /** Index A-Z en haut du tableau — pertinent pour un jeu trié par libellé (auteurs, mots-clés…). */
  alphabet?: AlphabetFilter;
  /** Pagination numérique — pertinente pour un jeu non alphabétique (dates, statuts…). */
  page?: number;
  totalPages?: number;
  onPageChange?: (page: number) => void;
};

const ALPHABET_THRESHOLD = 26;
const LETTERS = Array.from({ length: 26 }, (_, i) => String.fromCharCode(65 + i));

/**
 * Sélecteur plein écran pour choisir parmi un GRAND volume de données
 * (ex. rattacher une notice à un contenant parmi des milliers, choisir un
 * contact parmi les organisations partenaires). Construit sur `Modal`
 * (size="full") : croix de fermeture en haut, recherche + navigation
 * serveur — jamais de chargement de la liste complète côté client.
 *
 * Au-delà de {@link ALPHABET_THRESHOLD} résultats, deux aides à la navigation
 * sont disponibles, indépendamment l'une de l'autre selon ce que l'écran
 * appelant fournit :
 * - `alphabet` → bandeau des 26 lettres en haut (jeux triés par libellé) ;
 * - `page`/`totalPages`/`onPageChange` → pagination numérique 1..X (jeux non
 *   alphabétiques) ;
 * - les deux peuvent être fournis simultanément (ex. lettre choisie, puis
 *   pagination à l'intérieur de cette lettre).
 *
 * Squelette : le tri/filtre par colonne et la virtualisation de la liste
 * sont à brancher avec TanStack Table lors de l'implémentation par domaine.
 */
export function SelectionModal<T>({
  open,
  onClose,
  title,
  columns,
  rows,
  rowKey,
  multiple = false,
  onSearch,
  onConfirm,
  totalCount,
  alphabet,
  page = 1,
  totalPages = 1,
  onPageChange,
}: SelectionModalProps<T>) {
  const [selected, setSelected] = useState<Set<string | number>>(new Set());

  function toggle(key: string | number) {
    setSelected((prev) => {
      const next = multiple ? new Set(prev) : new Set<string | number>();
      next.has(key) ? next.delete(key) : next.add(key);
      return next;
    });
  }

  const total = totalCount ?? rows.length;
  const showNavigation = total > ALPHABET_THRESHOLD;
  const showAlphabet = showNavigation && !!alphabet;
  const showPagination = showNavigation && !!onPageChange && totalPages > 1;

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={title}
      size="full"
      footer={
        <div className="flex items-center justify-between">
          <span className="text-sm text-muted-foreground">{selected.size} sélectionné(s)</span>
          <div className="flex gap-2">
            <button type="button" onClick={onClose} className="rounded border border-border px-3 py-1.5 text-sm">
              Annuler
            </button>
            <button
              type="button"
              disabled={selected.size === 0}
              onClick={() => onConfirm(rows.filter((row) => selected.has(rowKey(row))))}
              className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-50"
            >
              Valider la sélection
            </button>
          </div>
        </div>
      }
    >
      <div className="flex h-full min-h-0 flex-col gap-3">
        {onSearch && (
          <input
            type="search"
            placeholder="Rechercher…"
            onChange={(e) => onSearch(e.target.value)}
            className="w-full max-w-sm shrink-0 rounded border border-border px-3 py-2 text-sm"
          />
        )}

        {showAlphabet && <AlphabetBar {...alphabet} />}

        {/* min-h-0 est indispensable ici : sans lui, ce conteneur grandirait à la
            taille du tableau au lieu de s'y limiter et de faire défiler seul
            son contenu (voir Modal.tsx pour la même règle sur le corps de la modale). */}
        <div className="min-h-0 flex-1 overflow-auto rounded border border-border">
          <table className="w-full text-left text-sm">
            <thead className="sticky top-0 bg-surface">
              <tr>
                <th className="w-10 px-3 py-2" />
                {columns.map((col) => (
                  <th key={col.key} className="px-3 py-2 font-medium text-muted-foreground">
                    {col.label}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {rows.map((row) => {
                const key = rowKey(row);
                const isSelected = selected.has(key);

                return (
                  <tr
                    key={key}
                    onClick={() => toggle(key)}
                    className={isSelected ? 'cursor-pointer bg-primary/10' : 'cursor-pointer hover:bg-muted'}
                  >
                    <td className="px-3 py-2">
                      <input type={multiple ? 'checkbox' : 'radio'} checked={isSelected} readOnly />
                    </td>
                    {columns.map((col) => (
                      <td key={col.key} className="px-3 py-2">
                        {col.render(row)}
                      </td>
                    ))}
                  </tr>
                );
              })}

              {rows.length === 0 && (
                <tr>
                  <td colSpan={columns.length + 1} className="px-3 py-6 text-center text-muted-foreground">
                    Aucun résultat.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        {showPagination && (
          <div className="flex shrink-0 items-center justify-center gap-2 text-sm">
            <button
              type="button"
              disabled={page <= 1}
              onClick={() => onPageChange!(page - 1)}
              className="rounded border border-border px-2 py-1 disabled:opacity-50"
            >
              Précédent
            </button>
            <span>
              Page {page} / {totalPages}
            </span>
            <button
              type="button"
              disabled={page >= totalPages}
              onClick={() => onPageChange!(page + 1)}
              className="rounded border border-border px-2 py-1 disabled:opacity-50"
            >
              Suivant
            </button>
          </div>
        )}
      </div>
    </Modal>
  );
}

/**
 * Bandeau des 26 lettres + "Tous", pour filtrer un jeu de données trié par
 * libellé. Exporté pour être réutilisé par d'autres modales construites sur
 * `Modal` qui ont besoin du même filtre sans le volume de `SelectionModal`
 * (ex. sélecteur de typologie).
 */
export function AlphabetBar({ activeLetter, onSelectLetter, availableLetters }: AlphabetFilter) {
  const letters = availableLetters ? LETTERS.filter((letter) => availableLetters.has(letter)) : LETTERS;

  return (
    <div className="flex shrink-0 flex-wrap gap-1 rounded border border-border bg-surface p-2">
      <button
        type="button"
        onClick={() => onSelectLetter(null)}
        className={clsx(
          'rounded px-2 py-1 text-xs font-semibold',
          !activeLetter ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
        )}
      >
        Tous
      </button>
      {letters.map((letter) => (
        <button
          key={letter}
          type="button"
          onClick={() => onSelectLetter(activeLetter === letter ? null : letter)}
          className={clsx(
            'h-7 w-7 rounded text-xs font-semibold',
            activeLetter === letter ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
          )}
        >
          {letter}
        </button>
      ))}
    </div>
  );
}
