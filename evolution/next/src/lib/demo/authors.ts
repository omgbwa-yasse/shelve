/**
 * Données factices — démonstration de `SelectionModal` en mode "index
 * alphabétique" (jeu trié par libellé, > 26 résultats). À remplacer par
 * `lib/api/endpoints/authors.ts` une fois le domaine Contacts/Auteurs porté
 * (voir PHASE-2-NEXTJS.md, étape 2.1).
 */
export type Author = {
  id: number;
  name: string;
  role: string;
};

const FIRST_NAMES = [
  'Aïcha', 'Bakary', 'Chantal', 'Denis', 'Élise', 'Fatou', 'Gaspard', 'Hélène',
  'Ibrahim', 'Julie', 'Karim', 'Léa', 'Moussa', 'Nadia', 'Omar', 'Patricia',
  'Quentin', 'Rania', 'Samuel', 'Thérèse', 'Umar', 'Valérie', 'Wilfried',
  'Xavier', 'Yasmine', 'Zoé',
];

const ROLES = ['Archiviste', 'Rédacteur', 'Signataire', 'Producteur', 'Correspondant'];

/** 130 auteurs factices, répartis sur les 26 lettres pour tester le bandeau A-Z. */
export const DEMO_AUTHORS: Author[] = LETTERS_RANGE().flatMap((letter, letterIndex) =>
  Array.from({ length: 5 }, (_, i) => {
    const id = letterIndex * 5 + i + 1;
    const first = FIRST_NAMES[(letterIndex + i) % FIRST_NAMES.length] ?? 'Camille';
    return {
      id,
      name: `${letter}${suffix(i)} ${first}`,
      role: ROLES[id % ROLES.length] ?? 'Archiviste',
    };
  }),
);

function LETTERS_RANGE(): string[] {
  return Array.from({ length: 26 }, (_, i) => String.fromCharCode(65 + i));
}

function suffix(i: number): string {
  // Complète la lettre en un nom-famille plausible et trié ("Aabot", "Aebrun", ...)
  return ['abot', 'ebrun', 'ivier', 'oussin', 'urand'][i] ?? '';
}

export type AuthorSearchParams = {
  letter?: string | null;
  query?: string;
};

export type AuthorSearchResult = {
  data: Author[];
  total: number;
};

/** Simule un aller-retour serveur (filtre par lettre et/ou recherche texte). */
export async function searchAuthorsDemo({ letter, query }: AuthorSearchParams): Promise<AuthorSearchResult> {
  await delay(150);

  let results = DEMO_AUTHORS;

  if (letter) {
    results = results.filter((author) => author.name.toUpperCase().startsWith(letter));
  }

  if (query) {
    const needle = query.trim().toLowerCase();
    results = results.filter((author) => author.name.toLowerCase().includes(needle));
  }

  return { data: results, total: DEMO_AUTHORS.length };
}

function delay(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
