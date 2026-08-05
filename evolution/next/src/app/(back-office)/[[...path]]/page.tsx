'use client';

import { useParams, useSearchParams } from 'next/navigation';
import { getFeatureSpecialRoute, resolveFeatureConfig } from '@/features';
import { ListScreen } from '@/components/crud/ListScreen';
import { FormScreen } from '@/components/crud/FormScreen';
import { DetailScreen } from '@/components/crud/DetailScreen';
import { FallbackScreen } from '@/components/crud/FallbackScreen';

/**
 * Routeur UNIVERSEL du back-office (composant client).
 *
 * Ordre de résolution :
 *   1. Écran spécialisé de la feature (features/<module>/resources.tsx).
 *   2. Config CRUD de la feature (liste / création / édition / détail).
 *   3. Écran de repli.
 */
export default function ResourceRouter() {
  const params = useParams<{ path?: string | string[] }>();
  const searchParams = useSearchParams();
  const raw = params.path;
  const segments = Array.isArray(raw) ? raw : raw ? [raw] : [];
  const fullPath = `/${segments.join('/')}`;

  const special = getFeatureSpecialRoute(fullPath);
  if (special) {
    const Component = special;
    const query = Object.fromEntries(searchParams.entries());
    return <Component tab={query.tab} action={query.action} view={query.view} />;
  }

  const config = resolveFeatureConfig(fullPath);

  if (!config) {
    return <FallbackScreen path={fullPath} />;
  }

  // Longueur (nb segments) du chemin de base réellement utilisé par la config.
  const base = configBase(config, segments);

  // Cas « création » : .../create
  if (segments.length === base + 1 && segments[segments.length - 1] === 'create') {
    return <FormScreen config={config} mode="create" />;
  }

  // Cas « édition » : .../{id}/edit
  if (segments.length === base + 2 && segments[segments.length - 1] === 'edit') {
    const id = segments[segments.length - 2];
    return <FormScreen config={config} mode="edit" id={id} />;
  }

  // Cas « détail » : .../{id}
  if (segments.length === base + 1) {
    const id = segments[segments.length - 1];
    return <DetailScreen config={config} id={String(id)} />;
  }

  // Cas « liste » : ... (chemin exact)
  return <ListScreen config={config} />;
}

/** Longueur (nb segments) du chemin de base réellement utilisé par la config. */
function configBase(config: ReturnType<typeof resolveFeatureConfig>, segments: string[]): number {
  const full = `/${segments.join('/')}`;
  const candidates = [config!.path, ...(config!.aliases ?? [])];
  const matched = candidates
    .filter((c) => c === full || full.startsWith(`${c}/`))
    .sort((a, b) => b.length - a.length)[0];

  return matched ? matched.split('/').filter(Boolean).length : segments.length;
}
