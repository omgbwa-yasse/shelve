'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { Icon } from '@/components/icons';
import type { FeatureRoute } from '@/lib/routing';
import type { OpacPage } from './services/opac-builder.service';
import * as api from './services/opac-builder.service';
import { OpacBuilder } from './components/OpacBuilder';

/**
 * Constructeur de page OPAC — point d'entrée back-office.
 * `/settings/opac-builder` → liste des pages ; `/…/pages/{id}` → éditeur.
 */
function PagesList() {
  const router = useRouter();
  const [pages, setPages] = useState<OpacPage[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    api
      .getPages()
      .then((r) => setPages(r.data))
      .catch(() => setError('Impossible de charger les pages.'));
  }, []);

  async function remove(page: OpacPage) {
    if (!window.confirm(`Supprimer la page « ${page.title} » ?`)) return;
    await api.deletePage(page.id);
    setPages((p) => (p ?? []).filter((x) => x.id !== page.id));
  }

  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold">Pages OPAC</h1>
          <p className="text-sm text-muted-foreground">
            Construisez le portail public avec des blocs (modules) et gardez plusieurs versions.
          </p>
        </div>
        <Link
          href="/settings/opac-builder/pages/create"
          className="inline-flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          <Icon name="plus" className="h-4 w-4" />
          Nouvelle page
        </Link>
      </header>

      {error && <p className="rounded-lg border border-danger/30 bg-danger/5 px-3 py-2 text-sm text-danger">{error}</p>}

      {pages === null ? (
        <div className="flex items-center justify-center gap-2 py-16 text-sm text-muted-foreground">
          <Icon name="refresh" className="h-4 w-4 animate-spin" /> Chargement…
        </div>
      ) : pages.length === 0 ? (
        <div className="rounded-lg border border-dashed border-border py-16 text-center text-sm text-muted-foreground">
          Aucune page. Créez votre première page pour ouvrir le constructeur.
        </div>
      ) : (
        <div className="overflow-hidden rounded-lg border border-border">
          <table className="w-full text-sm">
            <thead className="bg-surface text-left text-xs uppercase tracking-wide text-muted-foreground">
              <tr>
                <th className="px-4 py-2">Titre</th>
                <th className="px-4 py-2">Slug</th>
                <th className="px-4 py-2">Statut</th>
                <th className="px-4 py-2">Versions</th>
                <th className="px-4 py-2 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {pages.map((page) => (
                <tr key={page.id} className="border-t border-border bg-background">
                  <td className="px-4 py-2 font-medium">
                    {page.title}
                    {page.is_default && (
                      <span className="ml-2 rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-semibold text-primary">
                        par défaut
                      </span>
                    )}
                  </td>
                  <td className="px-4 py-2 font-mono text-xs">{page.slug}</td>
                  <td className="px-4 py-2 capitalize">
                    <span
                      className={`rounded px-1.5 py-0.5 text-[10px] font-semibold ${
                        page.status === 'published' ? 'bg-green-100 text-green-700' : 'bg-muted text-muted-foreground'
                      }`}
                    >
                      {page.status}
                    </span>
                  </td>
                  <td className="px-4 py-2">{page.versions_count ?? '—'}</td>
                  <td className="px-4 py-2 text-right">
                    <div className="inline-flex items-center gap-1.5">
                      <button
                        type="button"
                        onClick={() => router.push(`/settings/opac-builder/pages/${page.id}`)}
                        className="inline-flex items-center gap-1 rounded bg-primary px-2.5 py-1 text-xs font-medium text-primary-foreground hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                      >
                        <Icon name="layoutTemplate" className="h-3.5 w-3.5" />
                        Éditer
                      </button>
                      <button
                        type="button"
                        onClick={() => remove(page)}
                        className="rounded border border-border px-2 py-1 text-xs text-danger hover:bg-danger/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                      >
                        Supprimer
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}

function PageFormScreen({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const [title, setTitle] = useState('');
  const [slug, setSlug] = useState('');
  const [description, setDescription] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    if (mode !== 'edit' || !id) return;
    api.getPage(id).then((r) => {
      setTitle(r.data.page.title);
      setSlug(r.data.page.slug);
      setDescription(r.data.page.description ?? '');
    });
  }, [mode, id]);

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    setError(null);
    try {
      const payload = { title, slug, description };
      if (mode === 'edit' && id) {
        await api.updatePage(id, payload);
        router.push(`/settings/opac-builder/pages/${id}`);
      } else {
        const { data } = await api.createPage(payload);
        router.push(`/settings/opac-builder/pages/${data.id}`);
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Échec de l’enregistrement.');
      setLoading(false);
    }
  }

  return (
    <form onSubmit={submit} className="flex w-full max-w-2xl flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{mode === 'edit' ? 'Modifier la page' : 'Nouvelle page OPAC'}</h1>
        <button
          type="button"
          onClick={() => router.push('/settings/opac-builder')}
          className="rounded border border-border px-3 py-1.5 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          Annuler
        </button>
      </header>

      {error && <p className="rounded-lg border border-danger/30 bg-danger/5 px-3 py-2 text-sm text-danger">{error}</p>}

      <div className="grid grid-cols-1 gap-4 rounded-lg border border-border bg-surface p-4">
        <label className="flex flex-col gap-1 text-sm">
          <span className="font-medium">
            Titre <span className="text-danger">*</span>
          </span>
          <input
            required
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            className="rounded border border-border bg-background px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary"
          />
        </label>
        <label className="flex flex-col gap-1 text-sm">
          <span className="font-medium">
            Slug (URL) <span className="text-danger">*</span>
          </span>
          <input
            required
            value={slug}
            onChange={(e) => setSlug(e.target.value)}
            placeholder="home"
            className="rounded border border-border bg-background px-2 py-1.5 font-mono focus:outline-none focus:ring-1 focus:ring-primary"
          />
          <span className="text-xs text-muted-foreground">Ex. « home » pour la page d’accueil du portail.</span>
        </label>
        <label className="flex flex-col gap-1 text-sm">
          <span className="font-medium">Description</span>
          <textarea
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            rows={2}
            className="rounded border border-border bg-background px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary"
          />
        </label>
      </div>

      <footer className="flex justify-end">
        <button
          type="submit"
          disabled={loading}
          className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          {loading ? 'Enregistrement…' : 'Créer et ouvrir l’éditeur'}
        </button>
      </footer>
    </form>
  );
}

function BuilderScreen({ id }: { id: string }) {
  return <OpacBuilder pageId={id} />;
}

export const routes: FeatureRoute[] = [
  { path: '/settings/opac-builder', List: PagesList },
  {
    path: '/settings/opac-builder/pages',
    List: PagesList,
    Detail: BuilderScreen,
    Form: PageFormScreen,
  },
];
