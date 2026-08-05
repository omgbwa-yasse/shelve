'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useCreate } from '@/lib/api/hooks';
import { DataTable } from '@/components/ui/table';
import * as api from './services/public.service';
import { PublicDashboard, PublicInfo } from './components/public-views';
import type { FeatureRoute } from '@/lib/routing';

function makeList(r: ResourceApi, key: string, columns: TableColumn<Entity>[], o: { title: string; create?: string }) {
  return function List() {
    const { data, isLoading, isError } = useResourceList(r, key, { 'page.size': 50 } as never);
    const destroy = useDestroy(r, key);
    const rows = (data?.data ?? []) as Entity[];
    return (
      <div className="flex h-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{o.title}</h1>
          {o.create && <Link href={o.create} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link>}
        </header>
        <DataTable columns={columns} rows={rows} loading={isLoading} error={isError}
          actions={(row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>} />
      </div>
    );
  };
}

function makeForm(r: ResourceApi, key: string, o: { title: string; back: string; fields: { name: string; label: string; required?: boolean }[] }) {
  return function Form() {
    const router = useRouter();
    const create = useCreate(r, key);
    const [v, setV] = useState<Record<string, string>>({});
    async function submit(e: React.FormEvent) {
      e.preventDefault();
      await create.mutateAsync(v);
      router.push(o.back);
    }
    return (
      <form onSubmit={submit} className="flex w-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{o.title}</h1>
          <button type="button" onClick={() => router.push(o.back)} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
        </header>
        <div className="grid max-w-2xl grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2">
          {o.fields.map((f) => (
            <label key={f.name} className="flex flex-col gap-1 text-sm">
              <span>{f.label} {f.required && <span className="text-danger">*</span>}</span>
              <input value={v[f.name] ?? ''} onChange={(e) => setV((p) => ({ ...p, [f.name]: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
            </label>
          ))}
        </div>
        <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
      </form>
    );
  };
}

const TITLE_COLS: TableColumn<Entity>[] = [{ key: 'title', label: 'Titre' }];
const NAME_COLS: TableColumn<Entity>[] = [{ key: 'name', label: 'Nom' }];

export const routes: FeatureRoute[] = [
  { path: '/public/news', List: makeList(api.publicNewsApi, 'public-news', TITLE_COLS, { title: 'Actualités', create: '/public/news/create' }), Form: makeForm(api.publicNewsApi, 'public-news', { title: 'Actualité', back: '/public/news', fields: [{ name: 'title', label: 'Titre', required: true }] }) },
  { path: '/public/events', List: makeList(api.publicEventsApi, 'public-events', TITLE_COLS, { title: 'Événements', create: '/public/events/create' }), Form: makeForm(api.publicEventsApi, 'public-events', { title: 'Événement', back: '/public/events', fields: [{ name: 'title', label: 'Titre', required: true }] }) },
  { path: '/public/pages', List: makeList(api.publicPagesApi, 'public-pages', TITLE_COLS, { title: 'Pages publiques', create: '/public/pages/create' }), Form: makeForm(api.publicPagesApi, 'public-pages', { title: 'Page publique', back: '/public/pages', fields: [{ name: 'title', label: 'Titre', required: true }, { name: 'slug', label: 'Slug' }] }) },
  { path: '/public/templates', List: makeList(api.publicTemplatesApi, 'public-templates', NAME_COLS, { title: 'Templates publics', create: '/public/templates/create' }), Form: makeForm(api.publicTemplatesApi, 'public-templates', { title: 'Template', back: '/public/templates', fields: [{ name: 'name', label: 'Nom', required: true }] }) },
  { path: '/public/users', List: makeList(api.publicUsersApi, 'public-users', [{ key: 'name', label: 'Nom' }, { key: 'email', label: 'Email' }], { title: 'Utilisateurs publics', create: '/public/users/create' }), Form: makeForm(api.publicUsersApi, 'public-users', { title: 'Utilisateur public', back: '/public/users', fields: [{ name: 'name', label: 'Nom', required: true }, { name: 'email', label: 'Email' }] }) },
  { path: '/public/records', List: makeList(api.publicRecordsApi, 'public-records', NAME_COLS, { title: 'Notices publiques', create: '/public/records/create' }), Form: makeForm(api.publicRecordsApi, 'public-records', { title: 'Notice publique', back: '/public/records', fields: [{ name: 'name', label: 'Nom', required: true }] }) },
  { path: '/public/feedback', List: makeList(api.publicFeedbacksApi, 'public-feedbacks', [{ key: 'subject', label: 'Sujet' }], { title: 'Retours' }) },
  { path: '/public/search-logs', List: makeList(api.publicSearchLogsApi, 'public-search-logs', [{ key: 'query', label: 'Requête' }], { title: 'Journaux de recherche' }) },

  { path: '/public/dashboard', List: PublicDashboard },
  { path: '/public/statistics', List: PublicDashboard },
  { path: '/public/configurations', List: () => <PublicInfo title="Configuration OPAC" description="Paramètres généraux du portail public." note="Configurations OPAC gérées côté Laravel." /> },
  { path: '/public/opac-templates', List: () => <PublicInfo title="Templates OPAC" description="Modèles de rendu du portail public." note="Preview/duplication côté Laravel (R05 : jamais de rendu brut côté API)." /> },
  { path: '/public/document-requests', List: () => <PublicInfo title="Demandes de documents" description="Demandes des usagers publics." note="Seul POST store est exposé ; le CRUD d'administration reste côté Laravel." /> },
  { path: '/public/responses', List: () => <PublicInfo title="Réponses" description="Réponses aux demandes publiques." note="Seul POST store est exposé." /> },
  { path: '/public/response-attachments', List: () => <PublicInfo title="Pièces jointes de réponse" description="Pièces jointes associées aux réponses." note="Contrôleur API non routé." /> },
  { path: '/public/chats', List: () => <PublicInfo title="Chats publics" description="Conversations des usagers." note="PublicChat*ApiController créés mais non routés." /> },
  { path: '/public/chat-participants', List: () => <PublicInfo title="Participants aux chats" description="Participants des conversations publiques." note="Pas d'endpoint API." /> },
  { path: '/public/event-registrations', List: () => <PublicInfo title="Inscriptions aux événements" description="Inscriptions des usagers aux événements." note="Auto-enregistrement exposé ; administration complète côté Laravel." /> },
];
