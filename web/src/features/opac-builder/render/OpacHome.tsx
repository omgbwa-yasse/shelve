'use client';

/**
 * Rendu public de la page d'accueil OPAC construite avec le constructeur.
 * Récupère la version publiée (`/api/public/opac/home`) et rend les blocs
 * regroupés par zone, en appliquant les réglages du breakpoint courant.
 */
import { useMemo } from 'react';
import { useQuery } from '@tanstack/react-query';
import Link from 'next/link';
import { getPublicHome, type OpacBlock } from '../services/opac-builder.service';
import { useBreakpoint } from '../lib/use-breakpoint';
import { ModuleRenderer } from './modules';

export function OpacHome() {
  const breakpoint = useBreakpoint();
  const { data, isLoading, isError } = useQuery({
    queryKey: ['opac-public-home'],
    queryFn: getPublicHome,
  });

  const blocks = data?.data?.blocks ?? [];
  const version = data?.data?.version ?? null;

  const byZone = useMemo(() => {
    const map: Record<string, OpacBlock[]> = {};
    for (const block of blocks) {
      (map[block.zone] ??= []).push(block);
    }
    return map;
  }, [blocks]);

  if (isLoading) {
    return (
      <div className="mx-auto max-w-6xl px-4 py-12 text-center text-sm text-muted-foreground">
        Chargement du portail…
      </div>
    );
  }

  if (isError || (version === null && blocks.length === 0)) {
    return (
      <div className="mx-auto max-w-6xl px-4 py-16 text-center">
        <h1 className="font-serif text-2xl font-bold">Portail en construction</h1>
        <p className="mx-auto mt-2 max-w-xl text-muted-foreground">
          La page d’accueil n’est pas encore publiée. Utilisez le constructeur de pages OPAC
          pour composer la page puis publiez une version.
        </p>
        <Link
          href="/opac/search"
          className="mt-6 inline-block rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          Rechercher dans le catalogue
        </Link>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-6xl px-4 py-6">
      <div className="space-y-8">
        {renderZone(byZone['header'], 'header', breakpoint)}
        {renderZone(byZone['hero'], 'hero', breakpoint)}

        <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_300px]">
          <div className="space-y-8">{renderZone(byZone['main'], 'main', breakpoint)}</div>
          <aside className="space-y-8">{renderZone(byZone['sidebar'], 'sidebar', breakpoint)}</aside>
        </div>

        {renderZone(byZone['footer'], 'footer', breakpoint)}
      </div>
    </div>
  );
}

function renderZone(
  blocks: OpacBlock[] | undefined,
  zone: string,
  breakpoint: ReturnType<typeof useBreakpoint>,
) {
  if (!blocks || blocks.length === 0) return null;

  return (
    <>
      {blocks
        .slice()
        .sort((a, b) => a.order - b.order)
        .map((block) => (
          <ModuleRenderer key={`${zone}-${block.id ?? block.order}`} module={moduleOf(block)} block={block} breakpoint={breakpoint} />
        ))}
    </>
  );
}

function moduleOf(block: OpacBlock) {
  if (block.module?.key && block.module.settings) {
    return {
      key: block.module.key,
      label: block.module.label,
      description: null,
      icon: block.module.icon,
      variants: block.module.variants ?? [],
      settings: block.module.settings ?? [],
      zones: [],
      source: null,
    };
  }
  const variant = block.settings.variant as string | undefined;
  return {
    key: block.module_key,
    label: block.module?.label ?? block.module_key,
    description: null,
    icon: block.module?.icon ?? 'layoutTemplate',
    variants: variant ? [{ key: variant, label: variant }] : [],
    settings: [],
    zones: [],
    source: null,
  };
}
