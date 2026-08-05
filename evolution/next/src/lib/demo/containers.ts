/**
 * Données factices — démonstration de `SelectionModal` en mode "pagination
 * numérique" (jeu non alphabétique, > 26 résultats). À remplacer par
 * `lib/api/endpoints/containers.ts` une fois le domaine Dépôts porté (voir
 * PHASE-2-NEXTJS.md, étape 2.1).
 */
export type Container = {
  id: number;
  code: string;
  location: string;
  occupiedCm: number;
  capacityCm: number;
};

const ROOMS = ['Salle A', 'Salle B', 'Salle C', 'Sous-sol', 'Annexe'];

/** 84 contenants factices, pour tester la pagination (10 par page → 9 pages). */
export const DEMO_CONTAINERS: Container[] = Array.from({ length: 84 }, (_, i) => {
  const id = i + 1;
  const capacity = 100;
  return {
    id,
    code: `BOITE-${String(2024).slice(2)}-${String(id).padStart(4, '0')}`,
    location: `${ROOMS[id % ROOMS.length]} — Étagère ${1 + (id % 12)}`,
    occupiedCm: Math.round((id * 7) % capacity),
    capacityCm: capacity,
  };
});

export const CONTAINERS_PAGE_SIZE = 10;

export type ContainerSearchParams = {
  page?: number;
  query?: string;
};

export type ContainerSearchResult = {
  data: Container[];
  total: number;
  totalPages: number;
};

/** Simule un aller-retour serveur (recherche texte + pagination). */
export async function searchContainersDemo({ page = 1, query }: ContainerSearchParams): Promise<ContainerSearchResult> {
  await delay(150);

  let results = DEMO_CONTAINERS;

  if (query) {
    const needle = query.trim().toLowerCase();
    results = results.filter(
      (container) => container.code.toLowerCase().includes(needle) || container.location.toLowerCase().includes(needle),
    );
  }

  const total = results.length;
  const totalPages = Math.max(1, Math.ceil(total / CONTAINERS_PAGE_SIZE));
  const start = (page - 1) * CONTAINERS_PAGE_SIZE;
  const data = results.slice(start, start + CONTAINERS_PAGE_SIZE);

  return { data, total, totalPages };
}

function delay(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
