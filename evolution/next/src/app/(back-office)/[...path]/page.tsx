'use client';

import { useParams } from 'next/navigation';
import { resolveFeatureRoute } from '@/features';

/**
 * Routeur universel du back-office : dispatch vers les écrans propres à chaque
 * feature. Conventions : `/x` → List · `/x/create` → Form(create) ·
 * `/x/{id}` → Detail · `/x/{id}/edit` → Form(edit).
 */
export default function ResourceRouter() {
  const params = useParams<{ path: string | string[] }>();
  const raw = params.path;
  const segments = Array.isArray(raw) ? raw : [raw];
  const fullPath = `/${segments.join('/')}`;

  const hit = resolveFeatureRoute(fullPath);

  if (!hit) {
    return (
      <div className="flex h-full items-center justify-center">
        <p className="text-sm text-muted-foreground">Page introuvable.</p>
      </div>
    );
  }

  const { route, base } = hit;
  const baseLen = base.split('/').filter(Boolean).length;

  // Création : .../create
  if (segments.length === baseLen + 1 && segments[segments.length - 1] === 'create' && route.Form) {
    return <route.Form mode="create" />;
  }

  // Édition : .../{id}/edit
  if (segments.length === baseLen + 2 && segments[segments.length - 1] === 'edit' && route.Form) {
    return <route.Form mode="edit" id={segments[segments.length - 2]} />;
  }

  // Détail : .../{id}
  if (segments.length === baseLen + 1 && route.Detail) {
    return <route.Detail id={String(segments[segments.length - 1])} />;
  }

  // Liste (ou écran spécialisé sur son chemin exact)
  return <route.List />;
}
