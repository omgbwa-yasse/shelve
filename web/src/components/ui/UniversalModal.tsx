'use client';

import { useEffect, useMemo, useRef, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import clsx from 'clsx';
import { Modal } from '@/components/ui/Modal';
import type { TableColumn } from '@/components/ui/table';
import { Icon } from '@/components/icons';

const DEFAULT_PAGE_SIZE = 24;
const DEFAULT_SEARCH_MIN_CHARS = 0;
const ALPHABET_THRESHOLD = 12;
const LETTERS = Array.from({ length: 26 }, (_, i) => String.fromCharCode(65 + i));

export type AlphabetFilter = {
  /** Lettre active (surlignée), ou `null`/`undefined` = "Tous". */
  activeLetter?: string | null;
  /** Appelé avec la lettre cliquée, ou `null` si on reclique la lettre active ("Tous"). */
  onSelectLetter: (letter: string | null) => void;
  /** Restreint les lettres affichées à celles ayant au moins un résultat. */
  availableLetters?: Set<string>;
};

/**
 * Bandeau A-Z + « Tous », pour filtrer un jeu trié par libellé. Exposé pour
 * être réutilisé hors de `UniversalModal` quand seul le filtre est utile.
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

export type UniversalModalProps<T> = {
  open: boolean;
  onClose: () => void;
  title: string;
  /** Identifiant stable de chaque ligne (clé de sélection). */
  rowKey: (row: T) => string | number;
  /** Rendu de la sélection : `[row]` (immédiat) ou les lignes cochées (confirm). */
  onSelect: (selected: T[]) => void;

  /* --- Source de données : statique (rows) ou serveur (queryKey + queryFn) --- */
  rows?: T[];
  queryKey?: unknown[];
  queryFn?: () => Promise<{ data?: T[] }>;

  /* --- Affichage --- */
  /** `cards` (grille), `table` (colonnes), `list` (lignes simples), `tree` (arbre hiérarchique). */
  display?: 'cards' | 'table' | 'list' | 'tree';
  columns?: TableColumn<T>[];
  /** Rendu libre d'une carte (par défaut : titre + sous-titre). */
  renderCard?: (row: T) => React.ReactNode;
  cardTitleField?: keyof T & string;
  cardSubtitleField?: keyof T & string;
  /** Champ contenant l'id du parent (défaut `parent_id`) — utilisé par `display="tree"`. */
  treeParentField?: keyof T & string;

  /* --- Sélection --- */
  /** `true` = plusieurs lignes cochables ; `false` (défaut) = une seule. */
  multiple?: boolean;
  /**
   * `true` = cocher puis valider via le pied (« Valider la sélection ») ;
   * `false` = sélection immédiate au clic (défaut pour `cards`/`list`).
   */
  confirm?: boolean;
  /** Sélection initiale (clés), ex. valeurs déjà choisies dans un formulaire. */
  initialSelected?: (string | number)[];

  /* --- Recherche --- */
  searchable?: boolean;
  searchPlaceholder?: string;
  /** Seuil de déclenchement du filtre (0 = filtre dès la première frappe). */
  searchMinChars?: number;
  /** Filtre côté client. Par défaut : `name`/`description`/`code` contient la saisie. */
  filter?: (row: T, query: string) => boolean;
  /** Recherche côté serveur — le parent refetch puis renvoie des `rows` déjà filtrées. */
  onSearch?: (query: string) => void;

  /* --- Aides à la navigation --- */
  /** Bandeau A-Z calculé côté client (champ `alphabetField`). */
  alphabet?: boolean;
  alphabetField?: keyof T & string;
  /** Pagination côté client (tranches de `pageSize`). */
  pageSize?: number;
  /** Pagination côté serveur (contrôlée par le parent). */
  page?: number;
  totalPages?: number;
  onPageChange?: (page: number) => void;

  /* --- États --- */
  loading?: boolean;
  error?: boolean;
  emptyLabel?: string;
};

/** Filtre par défaut : la saisie doit apparaître dans name, description ou code. */
function defaultFilter(row: unknown, query: string): boolean {
  const o = row as Record<string, unknown>;
  return `${o.name ?? ''} ${o.description ?? ''} ${o.code ?? ''}`.toLowerCase().includes(query);
}

/**
 * MODALE UNIVERSELLE DE SÉLECTION de l'application — construite sur `Modal`
 * (taille `full`). Remplace `SelectionModal` et les modales de choix ad hoc :
 * un seul composant couvre toutes les éventualités d'un choix :
 *
 * - **source de données** : liste déjà chargée (`rows`) ou chargée par requête
 *   serveur (`queryKey` + `queryFn`, via React Query) ;
 * - **affichage** : grille de cartes (`cards`), tableau typé (`table`, colonnes
 *   libres), ou liste simple (`list`) ;
 * - **sélection** : simple ou multiple, immédiate au clic ou confirmée via le
 *   bouton « Valider la sélection » du pied ;
 * - **recherche** : filtre client (seuil configurable) et/ou recherche serveur ;
 * - **navigation** : bandeau alphabétique A-Z (client) et pagination (client
 *   `pageSize` ou serveur contrôlée) — indépendamment l'un de l'autre.
 *
 * Le tri/filtre par colonne côté serveur sur gros volumes reste à brancher via
 * TanStack Table lors de l'implémentation par domaine (voir PHILOSOPHY.md).
 */
export function UniversalModal<T>({
  open,
  onClose,
  title,
  rowKey,
  onSelect,
  rows: propRows,
  queryKey,
  queryFn,
  display = 'cards',
  columns,
  renderCard,
  cardTitleField = 'name' as keyof T & string,
  cardSubtitleField = 'description' as keyof T & string,
  treeParentField = 'parent_id' as keyof T & string,
  multiple = false,
  confirm = display === 'table',
  initialSelected,
  searchable = true,
  searchPlaceholder,
  searchMinChars = DEFAULT_SEARCH_MIN_CHARS,
  filter,
  onSearch,
  alphabet = display === 'cards',
  alphabetField = 'name' as keyof T & string,
  pageSize = DEFAULT_PAGE_SIZE,
  page: propPage,
  totalPages: propTotalPages,
  onPageChange,
  loading: propLoading,
  error: propError,
  emptyLabel = 'Aucun résultat.',
}: UniversalModalProps<T>) {
  const query = useQuery({
    queryKey: queryKey ?? ['type-picker', title],
    queryFn: queryFn ?? (() => Promise.resolve({ data: propRows ?? [] })),
    enabled: open && !!queryFn,
  });
  const rows = ((queryFn ? query.data?.data : propRows) ?? propRows ?? []) as T[];
  const loading = propLoading ?? (!!queryFn && query.isLoading);
  const error = propError ?? (!!queryFn && query.isError);

  const [search, setSearch] = useState('');
  const [activeLetter, setActiveLetter] = useState<string | null>(null);
  const [page, setPage] = useState(1);
  const [selected, setSelected] = useState<Set<string | number>>(new Set(initialSelected ?? []));
  // Nœuds dépliés du mode `tree` (clés de lignes). Vides = tout replié au départ.
  const [expanded, setExpanded] = useState<Set<string | number>>(new Set());

  // Réinitialise l'état interne à chaque réouverture de la modale.
  const prevOpen = useRef(open);
  useEffect(() => {
    if (open && !prevOpen.current) {
      setSearch('');
      setActiveLetter(null);
      setPage(1);
      setSelected(new Set(initialSelected ?? []));
      setExpanded(new Set());
    }
    prevOpen.current = open;
  }, [open, initialSelected]);

  /* --- Recherche : client (filter) et/ou serveur (onSearch) ----------------- */
  const trimmedSearch = search.trim().toLowerCase();
  const searchActive = trimmedSearch.length >= searchMinChars;
  // Si `onSearch` est fourni, le parent renvoie des `rows` déjà filtrées : on ne
  // refiltre pas ici. Sinon on applique le filtre client — `filter` ou, à défaut,
  // le filtre par défaut sur name/description/code.
  const match = filter ?? defaultFilter;
  let clientFiltered = searchActive && !onSearch ? rows.filter((r) => match(r, trimmedSearch)) : rows;

  /* --- Mode arbre (`display="tree"`) : hiérarchie parent/enfant --------------- */
  const isTree = display === 'tree';
  const parentOf = (row: T): string | null => {
    const v = (row as Record<string, unknown>)[treeParentField];
    if (v === null || v === undefined || v === '') return null;
    return String(v);
  };
  // En recherche, on ré-inclut les ancêtres des résultats pour que le chemin
  // menant à chaque correspondance reste visible (jamais un nœud orphelin).
  if (isTree && searchActive && !onSearch) {
    const matched = new Set(clientFiltered.map(rowKey));
    const byKey = new Map(rows.map((row) => [String(rowKey(row)), row]));
    const ancestors = new Set<string | number>();
    for (const row of rows) {
      if (!matched.has(String(rowKey(row)))) continue;
      let parentId = parentOf(row);
      while (parentId) {
        const parentRow = byKey.get(parentId);
        if (!parentRow || ancestors.has(parentId)) break;
        ancestors.add(parentId);
        parentId = parentOf(parentRow);
      }
    }
    clientFiltered = clientFiltered.concat(
      rows.filter((row) => ancestors.has(String(rowKey(row))) && !matched.has(String(rowKey(row)))),
    );
  }

  // Racines (sans parent) et enfants directs d'un nœud — pour construire l'arbre.
  const treeChildren = (parentId: string | null): T[] =>
    clientFiltered.filter((row) => parentOf(row) === parentId);

  const treeRoots = treeChildren(null);

  const toggleExpanded = (key: string | number) => {
    setExpanded((prev) => {
      const next = new Set(prev);
      if (next.has(key)) next.delete(key);
      else next.add(key);
      return next;
    });
  };

  /* --- Alphabet : lettres disponibles dans le jeu courant -------------------- */
  const availableLetters = useMemo(() => {
    if (!alphabet) return undefined;
    const letters = new Set<string>();
    for (const row of clientFiltered) {
      const value = String((row as Record<string, unknown>)[alphabetField] ?? '');
      if (value) letters.add(value.charAt(0).toUpperCase());
    }
    return letters;
  }, [alphabet, clientFiltered, alphabetField]);

  const effectiveLetter = activeLetter && availableLetters?.has(activeLetter) ? activeLetter : null;
  const byLetter = effectiveLetter
    ? clientFiltered.filter((row) =>
        String((row as Record<string, unknown>)[alphabetField] ?? '').toUpperCase().startsWith(effectiveLetter),
      )
    : clientFiltered;

  /* --- Pagination : client (pageSize) ou serveur (page/totalPages) ---------- */
  const isServerPaginated = typeof propPage === 'number' && typeof propTotalPages === 'number' && !!onPageChange;
  const totalPages = isServerPaginated
    ? (propTotalPages ?? 1)
    : Math.max(1, Math.ceil(byLetter.length / pageSize));
  const currentPage = isServerPaginated ? (propPage ?? 1) : Math.min(page, totalPages);
  const pageItems = isServerPaginated
    ? byLetter
    : byLetter.slice((currentPage - 1) * pageSize, currentPage * pageSize);

  const showAlphabet = alphabet && byLetter.length > ALPHABET_THRESHOLD && !isTree;
  const showPagination = totalPages > 1 && !isTree;

  /* --- Sélection ------------------------------------------------------------- */
  function toggle(key: string | number) {
    setSelected((prev) => {
      const next = multiple ? new Set(prev) : new Set<string | number>();
      if (next.has(key)) next.delete(key);
      else next.add(key);
      return next;
    });
  }

  function handleRowClick(row: T) {
    if (confirm) toggle(rowKey(row));
    else onSelect([row]);
  }

  function handleConfirm() {
    onSelect(byLetter.filter((row) => selected.has(rowKey(row))));
  }

  const isRowSelected = (row: T) => selected.has(rowKey(row));

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={title}
      size="full"
      search={
        searchable
          ? {
              value: search,
              onChange: (v) => {
                setSearch(v);
                setPage(1);
                onSearch?.(v);
              },
              placeholder: searchPlaceholder ?? 'Rechercher…',
            }
          : undefined
      }
      footer={
        <div className="flex flex-col gap-2">
          {showPagination && (
            <div className="flex items-center justify-center gap-2 text-sm">
              <button
                type="button"
                disabled={currentPage <= 1}
                onClick={() => (isServerPaginated ? onPageChange!(currentPage - 1) : setPage(currentPage - 1))}
                className="rounded border border-border px-2 py-1 disabled:opacity-50"
              >
                Précédent
              </button>
              <span>
                Page {currentPage} / {totalPages}
              </span>
              <button
                type="button"
                disabled={currentPage >= totalPages}
                onClick={() => (isServerPaginated ? onPageChange!(currentPage + 1) : setPage(currentPage + 1))}
                className="rounded border border-border px-2 py-1 disabled:opacity-50"
              >
                Suivant
              </button>
            </div>
          )}
          {confirm && (
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">{selected.size} sélectionné(s)</span>
              <div className="flex gap-2">
                <button type="button" onClick={onClose} className="rounded border border-border px-3 py-1.5 text-sm">
                  Annuler
                </button>
                <button
                  type="button"
                  disabled={selected.size === 0}
                  onClick={handleConfirm}
                  className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-50"
                >
                  Valider la sélection
                </button>
              </div>
            </div>
          )}
        </div>
      }
    >
      <div className="flex h-full min-h-0 flex-col gap-3">
        {showAlphabet && (
          <AlphabetBar
            activeLetter={effectiveLetter}
            onSelectLetter={(letter) => {
              setActiveLetter(letter);
              setPage(1);
            }}
            availableLetters={availableLetters}
          />
        )}

        {loading && <p className="text-sm text-muted-foreground">Chargement…</p>}
        {!loading && error && <p className="text-sm text-danger">Erreur de chargement</p>}
        {!loading && !error && (isTree ? treeRoots.length === 0 : pageItems.length === 0) && (
          <p className="text-sm text-muted-foreground">{emptyLabel}</p>
        )}

        {!loading && !error && isTree && treeRoots.length > 0 && (
          <div className="min-h-0 flex-1 overflow-auto rounded border border-border text-sm">
            {treeRoots.map((row) => (
              <TreeRow
                key={String(rowKey(row))}
                row={row}
                depth={0}
                expanded={expanded}
                rowKeyOf={rowKey}
                hasChildren={(r) => treeChildren(parentOf(r)).length > 0}
                childrenOf={(r) => treeChildren(parentOf(r))}
                onToggle={toggleExpanded}
                onPick={handleRowClick}
                confirm={confirm}
                isSelected={isRowSelected}
                renderLabel={renderCard ?? ((r) => String((r as Record<string, unknown>)[cardTitleField] ?? ''))}
                subtitleField={cardSubtitleField}
              />
            ))}
          </div>
        )}

        {!loading && !error && pageItems.length > 0 && display === 'cards' && (
          <div
            className={clsx('grid gap-3', {
              'grid-cols-1 sm:grid-cols-2': pageItems.length <= 4,
              'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3': pageItems.length > 4 && pageItems.length <= 9,
              'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4': pageItems.length > 9,
            })}
          >
            {pageItems.map((row) => (
              <button
                key={String(rowKey(row))}
                type="button"
                onClick={() => handleRowClick(row)}
                className={clsx(
                  'flex flex-col items-start rounded border px-3 py-2 text-left text-sm hover:bg-muted',
                  confirm && isRowSelected(row) ? 'border-primary bg-primary/10' : 'border-border hover:border-primary',
                )}
              >
                {renderCard ? (
                  renderCard(row)
                ) : (
                  <>
                    <span className="font-medium">{String((row as Record<string, unknown>)[cardTitleField] ?? '')}</span>
                    {String((row as Record<string, unknown>)[cardSubtitleField] ?? '') && (
                      <span className="text-xs text-muted-foreground">
                        {String((row as Record<string, unknown>)[cardSubtitleField])}
                      </span>
                    )}
                  </>
                )}
              </button>
            ))}
          </div>
        )}

        {!loading && !error && pageItems.length > 0 && display === 'list' && (
          <div className="flex min-h-0 flex-1 flex-col overflow-auto rounded border border-border text-sm">
            {pageItems.map((row) => (
              <button
                key={String(rowKey(row))}
                type="button"
                onClick={() => handleRowClick(row)}
                className={clsx(
                  'w-full px-3 py-2 text-left hover:bg-muted',
                  confirm && isRowSelected(row) ? 'bg-primary/10' : '',
                  'border-b border-border/60 last:border-b-0',
                )}
              >
                {renderCard ? renderCard(row) : String((row as Record<string, unknown>)[cardTitleField] ?? '')}
              </button>
            ))}
          </div>
        )}

        {!loading && !error && pageItems.length > 0 && display === 'table' && (
          <div className="min-h-0 flex-1 overflow-auto rounded border border-border">
            <table className="w-full text-left text-sm">
              <thead className="sticky top-0 bg-surface">
                <tr>
                  {confirm && <th className="w-10 px-3 py-2" />}
                  {(columns ?? []).map((col) => (
                    <th key={col.key} className="px-3 py-2 font-medium text-muted-foreground">
                      {col.label}
                    </th>
                  ))}
                  {(!columns || columns.length === 0) && <th className="px-3 py-2 font-medium text-muted-foreground">{title}</th>}
                </tr>
              </thead>
              <tbody>
                {pageItems.map((row) => {
                  const key = rowKey(row);
                  const isSelected = selected.has(key);
                  return (
                    <tr
                      key={String(key)}
                      onClick={() => handleRowClick(row)}
                      className={isSelected ? 'cursor-pointer bg-primary/10' : 'cursor-pointer hover:bg-muted'}
                    >
                      {confirm && (
                        <td className="px-3 py-2">
                          <input type={multiple ? 'checkbox' : 'radio'} checked={isSelected} readOnly />
                        </td>
                      )}
                      {(columns ?? []).map((col) => (
                        <td key={col.key} className="px-3 py-2">
                          {col.render ? col.render(row) : String((row as Record<string, unknown>)[col.key] ?? '—')}
                        </td>
                      ))}
                      {(!columns || columns.length === 0) && (
                        <td className="px-3 py-2">{String((row as Record<string, unknown>)[cardTitleField] ?? '—')}</td>
                      )}
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </Modal>
  );
}

/**
 * Ligne d'arbre hiérarchique (`display="tree"`) — bouton +/− pour déplier/replier,
 * clic sur le libellé pour sélectionner (immédiat ou cochable selon `confirm`).
 */
function TreeRow<T>({
  row,
  depth,
  expanded,
  rowKeyOf,
  hasChildren,
  childrenOf,
  onToggle,
  onPick,
  confirm,
  isSelected,
  renderLabel,
  subtitleField,
}: {
  row: T;
  depth: number;
  expanded: Set<string | number>;
  rowKeyOf: (row: T) => string | number;
  hasChildren: (row: T) => boolean;
  childrenOf: (row: T) => T[];
  onToggle: (key: string | number) => void;
  onPick: (row: T) => void;
  confirm: boolean;
  isSelected: (row: T) => boolean;
  renderLabel: (row: T) => React.ReactNode;
  subtitleField: keyof T & string;
}) {
  const key = String(rowKeyOf(row));
  const isExpanded = expanded.has(rowKeyOf(row));
  const hasKids = hasChildren(row);
  const selected = confirm ? isSelected(row) : undefined;
  const subtitle = String((row as Record<string, unknown>)[subtitleField] ?? '');

  return (
    <>
      <div
        role="treeitem"
        aria-expanded={hasKids ? isExpanded : undefined}
        className={clsx(
          'flex items-center gap-1.5 border-b border-border/60 px-2 py-1.5 last:border-b-0',
          'cursor-pointer hover:bg-muted',
          selected && 'bg-primary/10',
        )}
        style={{ paddingLeft: 8 + depth * 20 }}
      >
        {hasKids ? (
          <button
            type="button"
            onClick={() => onToggle(rowKeyOf(row))}
            aria-expanded={isExpanded}
            className="flex h-4 w-4 shrink-0 items-center justify-center rounded text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            title={isExpanded ? 'Replier' : 'Déplier'}
          >
            <Icon name={isExpanded ? 'chevronDown' : 'chevronRight'} className="h-3.5 w-3.5" />
          </button>
        ) : (
          <span className="flex h-4 w-4 shrink-0 items-center justify-center text-xs text-muted-foreground/40">·</span>
        )}
        {selected !== undefined && (
          <input type={confirm ? 'radio' : 'checkbox'} checked={selected} readOnly className="h-3.5 w-3.5 shrink-0" />
        )}
        <button type="button" onClick={() => onPick(row)} className="flex min-w-0 flex-1 items-center gap-2 text-left">
          <span className="truncate">{renderLabel(row)}</span>
          {subtitle && <span className="shrink-0 text-xs text-muted-foreground">{subtitle}</span>}
        </button>
      </div>
      {hasKids && isExpanded && (
        <div role="group">
          {childrenOf(row).map((child) => (
            <TreeRow
              key={String(rowKeyOf(child))}
              row={child}
              depth={depth + 1}
              expanded={expanded}
              rowKeyOf={rowKeyOf}
              hasChildren={hasChildren}
              childrenOf={childrenOf}
              onToggle={onToggle}
              onPick={onPick}
              confirm={confirm}
              isSelected={isSelected}
              renderLabel={renderLabel}
              subtitleField={subtitleField}
            />
          ))}
        </div>
      )}
    </>
  );
}
