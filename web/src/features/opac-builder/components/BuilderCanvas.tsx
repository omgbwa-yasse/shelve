'use client';

import { useState } from 'react';
import { Icon } from '@/components/icons';
import type { Breakpoint, OpacCatalog } from '../services/opac-builder.service';
import { blockHidden, type EditableBlock } from '../lib/builder';

const PREVIEW_WIDTH: Record<Breakpoint, string> = {
  mobile: 'max-w-[375px]',
  tablet: 'max-w-[768px]',
  desktop: 'max-w-none',
};

/**
 * Wireframe — zones de la page avec leurs blocs, réordonnables, déplaçables
 * et supprimables. La largeur du canvas suit le breakpoint courant pour
 * simuler le rendu mobile / tablette / PC.
 */
export function BuilderCanvas({
  catalog,
  blocks,
  selectedKey,
  breakpoint,
  onSelect,
  onMove,
  onMoveZone,
  onRemove,
  onAdd,
}: {
  catalog: OpacCatalog;
  blocks: EditableBlock[];
  selectedKey: string | null;
  breakpoint: Breakpoint;
  onSelect: (key: string) => void;
  onMove: (key: string, dir: -1 | 1) => void;
  onMoveZone: (key: string, zone: string) => void;
  onRemove: (key: string) => void;
  onAdd: (moduleKey: string, zone: string) => void;
}) {
  const moduleMap = new Map(catalog.modules.map((m) => [m.key, m]));

  return (
    <div className="flex flex-col gap-3">
      <div className={`mx-auto w-full rounded-xl border border-dashed border-border bg-background p-3 transition-all ${PREVIEW_WIDTH[breakpoint]}`}>
        {catalog.zones.map((zone) => {
          const zoneBlocks = blocks
            .filter((b) => b.zone === zone.key)
            .sort((a, b) => a.order - b.order);

          return (
            <section key={zone.key} className="mb-3 last:mb-0">
              <header className="mb-1 flex items-center justify-between text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                <span>{zone.label}</span>
                <span className="normal-case text-muted-foreground/70">{zoneBlocks.length} bloc(s)</span>
              </header>

              {zoneBlocks.length === 0 && (
                <div className="rounded border border-dashed border-border bg-muted/30 px-3 py-4 text-center text-xs text-muted-foreground">
                  Zone vide — ajoutez un module depuis la bibliothèque.
                </div>
              )}

              <div className="space-y-2">
                {zoneBlocks.map((block) => {
                  const module = moduleMap.get(block.module_key);
                  const isSelected = blockKey(block) === selectedKey;
                  const hidden = blockHidden(block, breakpoint);

                  return (
                    <article
                      key={blockKey(block)}
                      onClick={() => onSelect(blockKey(block))}
                      className={`group cursor-pointer rounded-lg border bg-surface p-3 transition ${
                        isSelected ? 'border-primary ring-1 ring-primary' : 'border-border hover:border-primary/40'
                      } ${hidden ? 'opacity-40' : ''}`}
                    >
                      <div className="flex items-center gap-2">
                        <span className="flex h-7 w-7 items-center justify-center rounded bg-muted text-muted-foreground">
                          <Icon name={(module?.icon as never) ?? 'layoutTemplate'} className="h-3.5 w-3.5" />
                        </span>
                        <div className="flex-1">
                          <p className="text-sm font-medium">{module?.label ?? block.module_key}</p>
                          <p className="text-xs text-muted-foreground">
                            {String(block.settings.variant ?? '—')}
                            {hidden && <span className="ml-2 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">masqué</span>}
                          </p>
                        </div>
                        <div className="flex items-center gap-0.5 opacity-0 transition group-hover:opacity-100" onClick={(e) => e.stopPropagation()}>
                          <button type="button" title="Monter" onClick={() => onMove(blockKey(block), -1)} className="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                            <Icon name="chevronUp" className="h-3.5 w-3.5" />
                          </button>
                          <button type="button" title="Descendre" onClick={() => onMove(blockKey(block), 1)} className="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                            <Icon name="chevronDown" className="h-3.5 w-3.5" />
                          </button>
                          <MoveZoneSelect block={block} zones={catalog.zones} onMoveZone={onMoveZone} />
                          <button type="button" title="Supprimer" onClick={() => onRemove(blockKey(block))} className="rounded p-1 text-danger/70 hover:bg-danger/10 hover:text-danger focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                            <Icon name="trash" className="h-3.5 w-3.5" />
                          </button>
                        </div>
                      </div>
                    </article>
                  );
                })}
              </div>

              <ZoneAdd zone={zone.key} moduleKeys={moduleAllowedForZone(catalog, zone.key)} onAdd={onAdd} />
            </section>
          );
        })}
      </div>
    </div>
  );
}

function blockKey(block: EditableBlock): string {
  return block._key;
}

function moduleAllowedForZone(catalog: OpacCatalog, zone: string): { key: string; label: string }[] {
  return catalog.modules
    .filter((m) => m.zones.includes(zone))
    .map((m) => ({ key: m.key, label: m.label }));
}

function MoveZoneSelect({
  block,
  zones,
  onMoveZone,
}: {
  block: EditableBlock;
  zones: OpacCatalog['zones'];
  onMoveZone: (key: string, zone: string) => void;
}) {
  return (
    <select
      value={block.zone}
      onChange={(e) => onMoveZone(blockKey(block), e.target.value)}
      title="Déplacer vers une zone"
      onClick={(e) => e.stopPropagation()}
      className="rounded border border-border bg-background px-1 py-0.5 text-[11px] focus:outline-none focus:ring-1 focus:ring-primary"
    >
      {zones.map((z) => (
        <option key={z.key} value={z.key}>
          {z.label}
        </option>
      ))}
    </select>
  );
}

function ZoneAdd({
  zone,
  moduleKeys,
  onAdd,
}: {
  zone: string;
  moduleKeys: { key: string; label: string }[];
  onAdd: (moduleKey: string, zone: string) => void;
}) {
  const [value, setValue] = useState('');

  if (moduleKeys.length === 0) return null;

  return (
    <div className="mt-1.5 flex items-center gap-1.5 text-xs">
      <select
        value={value}
        onChange={(e) => setValue(e.target.value)}
        className="rounded border border-border bg-background px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-primary"
      >
        <option value="">Ajouter un module…</option>
        {moduleKeys.map((m) => (
          <option key={m.key} value={m.key}>
            {m.label}
          </option>
        ))}
      </select>
      <button
        type="button"
        disabled={!value}
        onClick={() => {
          onAdd(value, zone);
          setValue('');
        }}
        className="rounded border border-primary/30 bg-primary/5 px-2 py-1 font-medium text-primary hover:bg-primary/10 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
      >
        +
      </button>
    </div>
  );
}
