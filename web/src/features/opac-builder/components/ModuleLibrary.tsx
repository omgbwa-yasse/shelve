'use client';

import { useState } from 'react';
import { Icon } from '@/components/icons';
import type { OpacCatalog, OpacModule } from '../services/opac-builder.service';

/**
 * Bibliothèque de modules — panneau gauche de l'éditeur. Chaque module du
 * catalogue propose ses zones autorisées ; cliquer sur une zone ajoute le
 * bloc correspondant au wireframe.
 */
export function ModuleLibrary({
  catalog,
  onAdd,
}: {
  catalog: OpacCatalog;
  onAdd: (moduleKey: string, zone: string) => void;
}) {
  const [query, setQuery] = useState('');
  const [expanded, setExpanded] = useState<Record<string, boolean>>({});

  const filtered = catalog.modules.filter((m) =>
    m.label.toLowerCase().includes(query.toLowerCase()),
  );

  return (
    <div className="flex h-full flex-col gap-3">
      <div>
        <h2 className="text-sm font-semibold">Bibliothèque de modules</h2>
        <p className="text-xs text-muted-foreground">Glissez un module dans une zone (ou cliquez).</p>
      </div>

      <input
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder="Filtrer les modules…"
        className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
      />

      <div className="flex-1 space-y-2 overflow-y-auto pr-1">
        {filtered.map((module) => (
          <ModuleCard
            key={module.key}
            module={module}
            zones={catalog.zones}
            expanded={Boolean(expanded[module.key])}
            onToggle={() => setExpanded((p) => ({ ...p, [module.key]: !p[module.key] }))}
            onAdd={onAdd}
          />
        ))}
        {filtered.length === 0 && (
          <p className="py-6 text-center text-xs text-muted-foreground">Aucun module.</p>
        )}
      </div>
    </div>
  );
}

function ModuleCard({
  module,
  zones,
  expanded,
  onToggle,
  onAdd,
}: {
  module: OpacModule;
  zones: OpacCatalog['zones'];
  expanded: boolean;
  onToggle: () => void;
  onAdd: (moduleKey: string, zone: string) => void;
}) {
  const allowed = zones.filter((z) => module.zones.includes(z.key));

  return (
    <div className="rounded-lg border border-border bg-surface p-3">
      <button type="button" onClick={onToggle} className="flex w-full items-center gap-2 text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
        <span className="flex h-8 w-8 items-center justify-center rounded-md bg-primary/10 text-primary">
          <Icon name={module.icon as never} className="h-4 w-4" />
        </span>
        <span className="flex-1">
          <span className="block text-sm font-medium">{module.label}</span>
          {module.description && (
            <span className="block text-xs text-muted-foreground">{module.description}</span>
          )}
        </span>
        <Icon name="chevronDown" className={`h-4 w-4 text-muted-foreground transition-transform ${expanded ? 'rotate-180' : ''}`} />
      </button>

      {expanded && (
        <div className="mt-2 flex flex-wrap gap-1.5 border-t border-border pt-2">
          {allowed.map((zone) => (
            <button
              key={zone.key}
              type="button"
              onClick={() => onAdd(module.key, zone.key)}
              className="inline-flex items-center gap-1 rounded border border-primary/30 bg-primary/5 px-2 py-1 text-xs font-medium text-primary transition hover:bg-primary/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              <Icon name="plus" className="h-3 w-3" />
              {zone.label}
            </button>
          ))}
          {module.variants.length > 0 && (
            <span className="w-full pt-1 text-[11px] text-muted-foreground">
              Variantes : {module.variants.map((v) => v.label).join(' · ')}
            </span>
          )}
        </div>
      )}
    </div>
  );
}
