'use client';

import { useCallback, useEffect, useMemo, useState } from 'react';
import { Icon } from '@/components/icons';
import type {
  Breakpoint,
  OpacCatalog,
  OpacPageVersion,
  PageDetail,
} from '../services/opac-builder.service';
import * as api from '../services/opac-builder.service';
import { ModuleLibrary } from './ModuleLibrary';
import { BuilderCanvas } from './BuilderCanvas';
import { BlockInspector } from './BlockInspector';
import { VersionPanel } from './VersionPanel';
import { blockKey, defaultSettings, toApiBlocks, withKeys, type EditableBlock } from '../lib/builder';

type Notice = { type: 'ok' | 'err'; text: string } | null;

/**
 * Constructeur de page OPAC — écran principal. Trois volets :
 * bibliothèque de modules (gauche), wireframe par zones (centre, largeur
 * selon le breakpoint), inspecteur du bloc sélectionné (droite). Les
 * versions sont gérées dans un panneau dépliable en pied d'éditeur.
 */
export function OpacBuilder({ pageId }: { pageId: string }) {
  const [catalog, setCatalog] = useState<OpacCatalog | null>(null);
  const [detail, setDetail] = useState<PageDetail | null>(null);
  const [current, setCurrent] = useState<OpacPageVersion | null>(null);
  const [blocks, setBlocks] = useState<EditableBlock[]>([]);
  const [selectedKey, setSelectedKey] = useState<string | null>(null);
  const [breakpoint, setBreakpoint] = useState<Breakpoint>('desktop');
  const [dirty, setDirty] = useState(false);
  const [busy, setBusy] = useState(false);
  const [notice, setNotice] = useState<Notice>(null);
  const [showVersions, setShowVersions] = useState(false);
  const [loaded, setLoaded] = useState(false);

  const moduleMap = useMemo(
    () => new Map((catalog?.modules ?? []).map((m) => [m.key, m])),
    [catalog],
  );

  // Chargement initial : catalogue + détail de la page.
  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const [cat, page] = await Promise.all([api.getModuleCatalog(), api.getPage(pageId)]);
        if (cancelled) return;
        setCatalog(cat.data);
        setDetail(page.data);
        pickInitialVersion(page.data.versions);
        setLoaded(true);
      } catch {
        if (!cancelled) {
          setNotice({ type: 'err', text: 'Impossible de charger la page ou le catalogue.' });
          setLoaded(true);
        }
      }
    })();
    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [pageId]);

  const pickInitialVersion = (versions: OpacPageVersion[]) => {
    const editable = versions.find((v) => v.status === 'draft');
    const active = versions.find((v) => v.is_active);
    const target = editable ?? active ?? versions[0];
    if (target) switchVersion(target.id, versions);
  };

  const switchVersion = async (versionId: number, versions?: OpacPageVersion[]) => {
    if (dirty && !window.confirm('Des modifications ne sont pas enregistrées. Continuer ?')) return;
    setNotice(null);
    setBusy(true);
    try {
      const { data: version } = await api.getVersion(versionId);
      setCurrent(version);
      setBlocks(withKeys(version.blocks ?? []));
      setSelectedKey(null);
      setDirty(false);
      if (versions) setDetail((d) => (d ? { ...d, versions } : d));
    } catch {
      setNotice({ type: 'err', text: 'Impossible de charger la version.' });
    } finally {
      setBusy(false);
    }
  };

  const markDirty = useCallback((next: EditableBlock[]) => {
    setBlocks(next);
    setDirty(true);
  }, []);

  const addBlock = (moduleKey: string, zone: string) => {
    const module = moduleMap.get(moduleKey);
    if (!module) return;
    const settings = defaultSettings(module);
    settings.variant = module.variants[0]?.key ?? 'default';
    const zoneBlocks = blocks.filter((b) => b.zone === zone);
    const next: EditableBlock = {
      _key: `new-${Date.now()}`,
      zone,
      module_key: moduleKey,
      settings,
      responsive: null,
      order: zoneBlocks.length,
    };
    markDirty([...blocks, next]);
    setSelectedKey(next._key);
  };

  const removeBlock = (key: string) => {
    const kept = blocks.filter((b) => blockKey(b) !== key);
    markDirty(kept.map((b, i) => ({ ...b, order: i })));
    if (selectedKey === key) setSelectedKey(null);
  };

  const moveBlock = (key: string, dir: -1 | 1) => {
    const index = blocks.findIndex((b) => blockKey(b) === key);
    const block = blocks[index];
    if (!block) return;
    const zone = block.zone;
    const zoneIndexes = blocks
      .map((b, i) => ({ b, i }))
      .filter(({ b }) => b.zone === zone);
    const pos = zoneIndexes.findIndex(({ b }) => blockKey(b) === key);
    const target = pos + dir;
    if (target < 0 || target >= zoneIndexes.length) return;
    const fromItem = zoneIndexes[pos];
    const toItem = zoneIndexes[target];
    if (!fromItem || !toItem) return;
    const next = [...blocks];
    const from = fromItem.i;
    const to = toItem.i;
    const a = next[from];
    const b = next[to];
    if (!a || !b) return;
    next[from] = b;
    next[to] = a;
    markDirty(next.map((blk, i) => ({ ...blk, order: i })));
  };

  const moveBlockZone = (key: string, zone: string) => {
    const next = blocks.map((b) => {
      if (blockKey(b) !== key) return b;
      const zoneBlocks = blocks.filter((x) => x.zone === zone && blockKey(x) !== key);
      return { ...b, zone, order: zoneBlocks.length };
    });
    markDirty(next.map((b, i) => ({ ...b, order: i })));
  };

  const updateBlock = (next: EditableBlock) => {
    markDirty(blocks.map((b) => (blockKey(b) === next._key ? next : b)));
  };

  const save = async () => {
    if (!current) return;
    setBusy(true);
    setNotice(null);
    try {
      const { data: updated } = await api.updateVersion(current.id, { blocks: toApiBlocks(blocks) });
      setCurrent(updated);
      setBlocks(withKeys(updated.blocks ?? []));
      setDirty(false);
      setNotice({ type: 'ok', text: 'Version enregistrée.' });
    } catch (e) {
      setNotice({ type: 'err', text: formatError(e) });
    } finally {
      setBusy(false);
    }
  };

  const createNewVersion = async () => {
    if (!detail || !current) return;
    if (dirty && !window.confirm('Les modifications en cours seront perdues. Continuer ?')) return;
    setBusy(true);
    setNotice(null);
    try {
      const name = `Version du ${new Date().toLocaleString('fr-FR')}`;
      const { data: created } = await api.createVersion(detail.page.id, {
        name,
        source_version_id: current.id,
      });
      const versions = [...(detail?.versions ?? []), created];
      setDetail({ ...detail, versions });
      setCurrent(created);
      setBlocks(withKeys(created.blocks ?? []));
      setDirty(false);
      setSelectedKey(null);
      setNotice({ type: 'ok', text: `Nouvelle version créée.` });
    } catch (e) {
      setNotice({ type: 'err', text: formatError(e) });
    } finally {
      setBusy(false);
    }
  };

  const activate = async (id: number) => {
    setBusy(true);
    setNotice(null);
    try {
      const { data: updated } = await api.activateVersion(id);
      refreshAfterVersionChange(updated);
      setNotice({ type: 'ok', text: 'Version publiée — elle est désormais affichée sur le portail.' });
    } catch (e) {
      setNotice({ type: 'err', text: formatError(e) });
    } finally {
      setBusy(false);
    }
  };

  const duplicate = async (id: number) => {
    setBusy(true);
    setNotice(null);
    try {
      const { data: created } = await api.duplicateVersion(id);
      if (detail) setDetail({ ...detail, versions: [...detail.versions, created] });
      setNotice({ type: 'ok', text: 'Version dupliquée.' });
    } catch (e) {
      setNotice({ type: 'err', text: formatError(e) });
    } finally {
      setBusy(false);
    }
  };

  const restore = async (id: number) => {
    setBusy(true);
    setNotice(null);
    try {
      const { data: updated } = await api.restoreVersion(id);
      refreshAfterVersionChange(updated);
      setNotice({ type: 'ok', text: 'Version restaurée et publiée.' });
    } catch (e) {
      setNotice({ type: 'err', text: formatError(e) });
    } finally {
      setBusy(false);
    }
  };

  const removeVersion = async (id: number) => {
    if (!window.confirm('Supprimer cette version ?')) return;
    setBusy(true);
    setNotice(null);
    try {
      await api.deleteVersion(id);
      const versions = (detail?.versions ?? []).filter((v) => v.id !== id);
      setDetail((d) => (d ? { ...d, versions } : d));
      if (current?.id === id) {
        setCurrent(null);
        setBlocks([]);
      }
      setNotice({ type: 'ok', text: 'Version supprimée.' });
    } catch (e) {
      setNotice({ type: 'err', text: formatError(e) });
    } finally {
      setBusy(false);
    }
  };

  const refreshAfterVersionChange = (updated: OpacPageVersion) => {
    setDetail((d) =>
      d ? { ...d, versions: d.versions.map((v) => (v.id === updated.id ? updated : { ...v, is_active: false })) } : d,
    );
    setCurrent(updated);
    setBlocks(withKeys(updated.blocks ?? []));
    setSelectedKey(null);
    setDirty(false);
  };

  if (!loaded) {
    return (
      <div className="flex h-full items-center justify-center gap-2 text-sm text-muted-foreground">
        <Icon name="refresh" className="h-4 w-4 animate-spin" />
        Chargement de l'éditeur…
      </div>
    );
  }

  if (!catalog || !detail) {
    return <p className="p-6 text-sm text-danger">{notice?.text ?? 'Page introuvable.'}</p>;
  }

  const selected = blocks.find((b) => blockKey(b) === selectedKey) ?? null;
  const selectedModule = selected ? moduleMap.get(selected.module_key) : undefined;

  return (
    <div className="flex h-full flex-col gap-3">
      {/* ===== Barre supérieure ===== */}
      <header className="flex flex-wrap items-center gap-3 rounded-lg border border-border bg-surface p-3">
        <div className="flex min-w-0 flex-1 items-center gap-3">
          <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
            <Icon name="layoutTemplate" className="h-5 w-5" />
          </div>
          <div className="min-w-0">
            <h1 className="truncate font-serif text-lg font-bold">{detail.page.title}</h1>
            <p className="truncate text-xs text-muted-foreground">
              /{detail.page.slug} · {current?.name ?? '—'}
            </p>
          </div>
        </div>

        <VersionSwitcher versions={detail.versions} currentId={current?.id ?? null} onSwitch={(id) => switchVersion(id)} />

        <div className="flex items-center gap-2">
          {dirty && <span className="text-xs font-medium text-amber-600">● modifications non enregistrées</span>}
          <button
            type="button"
            onClick={() => setShowVersions((v) => !v)}
            className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            Versions
          </button>
          <button
            type="button"
            onClick={save}
            disabled={busy || !dirty || !current?.is_editable}
            className="inline-flex items-center gap-1.5 rounded border border-border bg-background px-3 py-1.5 text-sm hover:bg-muted disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            <Icon name="save" className="h-4 w-4" />
            Enregistrer
          </button>
          <button
            type="button"
            onClick={() => activate(current?.id ?? 0)}
            disabled={busy || !current}
            className="inline-flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            <Icon name="rocket" className="h-4 w-4" />
            Publier
          </button>
        </div>
      </header>

      {notice && (
        <div className={`rounded-lg border px-3 py-2 text-sm ${notice.type === 'ok' ? 'border-green-200 bg-green-50 text-green-700' : 'border-danger/30 bg-danger/5 text-danger'}`}>
          {notice.text}
        </div>
      )}

      {current && !current.is_editable && (
        <div className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-700">
          Cette version est publiée et immuable — dupliquez-la pour créer un brouillon éditable.
        </div>
      )}

      {/* ===== Corps : 3 volets ===== */}
      <div className="grid flex-1 gap-3 overflow-hidden lg:grid-cols-[260px_minmax(0,1fr)_300px]">
        <aside className="min-h-0 overflow-y-auto rounded-lg border border-border bg-surface p-3">
          <ModuleLibrary catalog={catalog} onAdd={addBlock} />
        </aside>

        <main className="min-h-0 overflow-y-auto rounded-lg border border-border bg-surface p-4">
          <div className="mb-3 flex items-center justify-between">
            <h2 className="text-sm font-semibold">Wireframe</h2>
            <div className="flex gap-1">
              {(['mobile', 'tablet', 'desktop'] as Breakpoint[]).map((bp) => (
                <button
                  key={bp}
                  type="button"
                  onClick={() => setBreakpoint(bp)}
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
          <BuilderCanvas
            catalog={catalog}
            blocks={blocks}
            selectedKey={selectedKey}
            breakpoint={breakpoint}
            onSelect={setSelectedKey}
            onMove={moveBlock}
            onMoveZone={moveBlockZone}
            onRemove={removeBlock}
            onAdd={addBlock}
          />
        </main>

        <aside className="min-h-0 overflow-y-auto rounded-lg border border-border bg-surface p-3">
          {selected && selectedModule ? (
            <BlockInspector
              module={selectedModule}
              block={selected}
              breakpoint={breakpoint}
              onBreakpointChange={setBreakpoint}
              onChange={updateBlock}
            />
          ) : (
            <div className="flex h-full items-center justify-center">
              <p className="text-center text-xs text-muted-foreground">
                Sélectionnez un bloc dans le wireframe pour éditer ses réglages et son adaptation mobile / tablette / PC.
              </p>
            </div>
          )}
        </aside>
      </div>

      {/* ===== Panneau versions ===== */}
      {showVersions && (
        <div className="rounded-lg border border-border bg-surface p-3">
          <VersionPanel
            versions={detail.versions}
            currentId={current?.id ?? null}
            busy={busy}
            onSwitch={(id) => switchVersion(id)}
            onCreate={createNewVersion}
            onActivate={activate}
            onDuplicate={duplicate}
            onRestore={restore}
            onDelete={removeVersion}
          />
        </div>
      )}
    </div>
  );
}

function VersionSwitcher({
  versions,
  currentId,
  onSwitch,
}: {
  versions: OpacPageVersion[];
  currentId: number | null;
  onSwitch: (id: number) => void;
}) {
  return (
    <select
      value={currentId ?? ''}
      onChange={(e) => onSwitch(Number(e.target.value))}
      className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
    >
      {versions.map((v) => (
        <option key={v.id} value={v.id}>
          {v.name} — {v.status}
          {v.is_active ? ' (active)' : ''}
        </option>
      ))}
    </select>
  );
}

function formatError(e: unknown): string {
  if (e instanceof Error) return e.message;
  return 'Une erreur est survenue.';
}
