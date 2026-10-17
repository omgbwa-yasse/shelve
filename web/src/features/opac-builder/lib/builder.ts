/**
 * Utilitaires du constructeur OPAC — résolution des réglages par breakpoint
 * et manipulations immuables de blocs.
 */
import type { Breakpoint, OpacBlock, OpacModule } from '../services/opac-builder.service';

/** Réglages par défaut d'un module (clé → valeur). */
export function defaultSettings(module: OpacModule): Record<string, unknown> {
  const defaults: Record<string, unknown> = {};
  for (const setting of module.settings) {
    defaults[setting.key] = setting.default ?? null;
  }
  return defaults;
}

/** Champs surchargeables par breakpoint. */
function responsiveKeys(module: OpacModule): Set<string> {
  return new Set(
    module.settings.filter((s) => s.responsive).map((s) => s.key),
  );
}

/** Réglages effectifs d'un bloc pour un breakpoint donné. */
export function effectiveSettings(
  module: OpacModule,
  block: OpacBlock,
  breakpoint: Breakpoint,
): Record<string, unknown> {
  const base = { ...defaultSettings(module), ...block.settings };
  const overrides = block.responsive?.[breakpoint] ?? {};
  for (const key of responsiveKeys(module)) {
    if (key in overrides) base[key] = overrides[key];
  }
  return base;
}

/** Le bloc est-il masqué sur ce breakpoint ? */
export function blockHidden(block: OpacBlock, breakpoint: Breakpoint): boolean {
  return Boolean(block.responsive?.[breakpoint]?.['hidden']);
}

/**
 * Définit un réglage pour un breakpoint. Sur `desktop`, l'écriture va dans
 * `settings` ; sur mobile/tablette, dans `responsive[breakpoint]` (seuls les
 * champs `responsive` y sont stockés). Renvoie un NOUVEAU bloc.
 */
export function setSetting<T extends OpacBlock>(
  block: T,
  module: OpacModule,
  breakpoint: Breakpoint,
  key: string,
  value: unknown,
): T {
  const next = structuredClone(block) as T;

  if (breakpoint === 'desktop') {
    next.settings = { ...next.settings, [key]: value };
    return next;
  }

  const responsive = { ...(next.responsive ?? {}) };
  const overrides = { ...(responsive[breakpoint] ?? {}) };
  overrides[key] = value;
  responsive[breakpoint] = overrides;
  next.responsive = responsive;
  return next;
}

/** Masque / affiche le bloc sur un breakpoint donné. */
export function setHidden<T extends OpacBlock>(block: T, breakpoint: Breakpoint, hidden: boolean): T {
  const next = structuredClone(block) as T;
  const responsive = { ...(next.responsive ?? {}) };
  const overrides = { ...(responsive[breakpoint] ?? {}) };
  if (hidden) overrides['hidden'] = true;
  else delete overrides['hidden'];
  if (Object.keys(overrides).length > 0) responsive[breakpoint] = overrides;
  else delete responsive[breakpoint];
  next.responsive = Object.keys(responsive).length > 0 ? responsive : null;
  return next;
}

/** Valeur d'un réglage en tenant compte du breakpoint. */
export function settingValue(
  block: OpacBlock,
  module: OpacModule,
  breakpoint: Breakpoint,
  key: string,
): unknown {
  return effectiveSettings(module, block, breakpoint)[key] ?? null;
}

/** Clé stable pour React (id backend sinon clé locale `_key`). */
export function blockKey(block: OpacBlock): string {
  return String((block as EditableBlock)._key ?? block.id ?? '');
}

export type EditableBlock = OpacBlock & { _key: string };

/** Dote une liste de blocs d'une clé locale stable. */
export function withKeys(blocks: OpacBlock[]): EditableBlock[] {
  let counter = 0;
  const used = new Set(blocks.map((b) => String(b.id)).filter(Boolean));
  return blocks.map((b) => {
    if (b.id) return { ...b, _key: `id-${b.id}` };
    while (used.has(`new-${counter}`)) counter++;
    used.add(`new-${counter}`);
    return { ...b, _key: `new-${counter++}` };
  });
}

/** Sérialise les blocs pour l'API (retire la clé locale). */
export function toApiBlocks(blocks: EditableBlock[]): OpacBlock[] {
  return blocks.map(({ _key: _k, ...rest }) => rest);
}

/** Réordonne une liste de blocs d'une zone après chaque mutation. */
export function reorder(blocks: EditableBlock[]): EditableBlock[] {
  return blocks.map((b, index) => ({ ...b, order: index }));
}
