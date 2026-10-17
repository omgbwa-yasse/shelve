'use client';

import { Icon } from '@/components/icons';
import type { Breakpoint, OpacModule, OpacModuleSetting } from '../services/opac-builder.service';
import { blockHidden, effectiveSettings, setHidden, setSetting, type EditableBlock } from '../lib/builder';

/**
 * Inspecteur de bloc — panneau droit de l'éditeur. Génère le formulaire de
 * réglages depuis le schéma du module (`OpacModule.settings`).
 *
 * Les champs `responsive` sont éditables par breakpoint : sur desktop ils
 * alimentent `settings`, sur mobile/tablette ils écrivent dans
 * `responsive[breakpoint]` (avec indicateur « surchargé » + réinitialisation).
 */
export function BlockInspector({
  module,
  block,
  breakpoint,
  onBreakpointChange,
  onChange,
}: {
  module: OpacModule;
  block: EditableBlock;
  breakpoint: Breakpoint;
  onBreakpointChange: (bp: Breakpoint) => void;
  onChange: (block: EditableBlock) => void;
}) {
  const effective = effectiveSettings(module, block, breakpoint);
  const hidden = blockHidden(block, breakpoint);

  const setVariant = (variant: string) => {
    onChange({ ...block, settings: { ...block.settings, variant } });
  };

  const update = (key: string, value: unknown) => {
    onChange(setSetting(block, module, breakpoint, key, value));
  };

  const reset = (key: string) => {
    const next = structuredClone(block);
    const responsive = { ...(next.responsive ?? {}) };
    const overrides = { ...(responsive[breakpoint] ?? {}) };
    delete overrides[key];
    if (Object.keys(overrides).length > 0) responsive[breakpoint] = overrides;
    else delete responsive[breakpoint];
    next.responsive = Object.keys(responsive).length > 0 ? responsive : null;
    onChange(next);
  };

  return (
    <div className="flex h-full flex-col gap-4">
      <div>
        <div className="flex items-center gap-2">
          <span className="flex h-8 w-8 items-center justify-center rounded-md bg-primary/10 text-primary">
            <Icon name={(module.icon as never) ?? 'layoutTemplate'} className="h-4 w-4" />
          </span>
          <div>
            <h2 className="text-sm font-semibold">{module.label}</h2>
            <p className="text-xs text-muted-foreground">{block.zone}</p>
          </div>
        </div>
        {module.description && <p className="mt-2 text-xs text-muted-foreground">{module.description}</p>}
      </div>

      {/* Variante */}
      {module.variants.length > 0 && (
        <label className="flex flex-col gap-1 text-sm">
          <span className="font-medium">Variante</span>
          <select
            value={String(block.settings.variant ?? module.variants[0]?.key ?? '')}
            onChange={(e) => setVariant(e.target.value)}
            className="rounded border border-border bg-background px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary"
          >
            {module.variants.map((v) => (
              <option key={v.key} value={v.key}>
                {v.label}
              </option>
            ))}
          </select>
        </label>
      )}

      {/* Visibilité par breakpoint */}
      <div className="rounded-lg border border-border bg-surface p-3">
        <div className="mb-2 flex items-center justify-between">
          <span className="text-xs font-medium">Aperçu / édition</span>
          <div className="flex gap-1">
            {(['mobile', 'tablet', 'desktop'] as Breakpoint[]).map((bp) => (
              <button
                key={bp}
                type="button"
                onClick={() => onBreakpointChange(bp)}
                className={`flex items-center gap-1 rounded px-2 py-1 text-xs font-medium capitalize transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${
                  breakpoint === bp ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground hover:bg-muted/70'
                }`}
              >
                <Icon name={bp === 'mobile' ? 'smartphone' : bp === 'tablet' ? 'tablet' : 'monitor'} className="h-3.5 w-3.5" />
                {bp}
              </button>
            ))}
          </div>
        </div>
        <label className="flex items-center gap-2 text-sm">
          <input
            type="checkbox"
            checked={hidden}
            onChange={(e) => onChange(setHidden(block, breakpoint, e.target.checked))}
            className="h-4 w-4"
          />
          Masquer ce bloc sur {breakpoint === 'desktop' ? 'PC' : breakpoint === 'tablet' ? 'tablette' : 'mobile'}
        </label>
        {breakpoint !== 'desktop' && (
          <p className="mt-1 text-[11px] text-muted-foreground">
            Les champs marqués « responsive » sont surchargés pour cet écran.
          </p>
        )}
      </div>

      {/* Réglages du module */}
      <div className="flex-1 space-y-3 overflow-y-auto pr-1">
        {module.settings.map((setting) => {
          const isResponsive = Boolean(setting.responsive);
          const isOverride = isResponsive && breakpoint !== 'desktop' && breakpoint in (block.responsive ?? {}) && setting.key in (block.responsive?.[breakpoint] ?? {});
          const value = effective[setting.key];

          return (
            <div key={setting.key} className="flex flex-col gap-1 text-sm">
              <span className="flex items-center gap-1.5 font-medium">
                {setting.label}
                {isOverride && (
                  <button
                    type="button"
                    title="Réinitialiser (valeur PC)"
                    onClick={() => reset(setting.key)}
                    className="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 hover:bg-amber-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                  >
                    surchargé · réinit
                  </button>
                )}
              </span>
              <Field setting={setting} value={value} onChange={(v) => update(setting.key, v)} />
              {isResponsive && breakpoint === 'desktop' && (
                <span className="text-[11px] text-muted-foreground">Adaptable par écran (mobile / tablette).</span>
              )}
            </div>
          );
        })}
        {module.settings.length === 0 && (
          <p className="text-xs text-muted-foreground">Aucun réglage pour ce module.</p>
        )}
      </div>
    </div>
  );
}

function Field({
  setting,
  value,
  onChange,
}: {
  setting: OpacModuleSetting;
  value: unknown;
  onChange: (value: unknown) => void;
}) {
  switch (setting.type) {
    case 'textarea':
      return (
        <textarea
          value={String(value ?? '')}
          onChange={(e) => onChange(e.target.value)}
          rows={4}
          className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
        />
      );
    case 'number':
      return (
        <input
          type="number"
          min={setting.min}
          max={setting.max}
          value={Number(value ?? setting.default ?? 0)}
          onChange={(e) => onChange(Number(e.target.value))}
          className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
        />
      );
    case 'boolean':
      return (
        <input type="checkbox" checked={Boolean(value)} onChange={(e) => onChange(e.target.checked)} className="h-4 w-4" />
      );
    case 'select':
      return (
        <select
          value={String(value ?? '')}
          onChange={(e) => onChange(e.target.value)}
          className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
        >
          {(setting.options ?? []).map((o) => (
            <option key={o.value} value={o.value}>
              {o.label}
            </option>
          ))}
        </select>
      );
    case 'url':
      return (
        <input
          type="url"
          value={String(value ?? '')}
          onChange={(e) => onChange(e.target.value)}
          placeholder="https://…"
          className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
        />
      );
    default:
      return (
        <input
          type="text"
          value={String(value ?? '')}
          onChange={(e) => onChange(e.target.value)}
          className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
        />
      );
  }
}
