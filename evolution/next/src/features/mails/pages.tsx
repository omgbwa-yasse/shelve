'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useResource, useCreate, useUpdate } from '@/lib/api/hooks';
import { DataTable, Pagination } from '@/components/ui/table';
import { PageHeader } from '@/components/ui/page';
import * as api from './services/mail.service';
import { ParapheurActions } from './components/parapheur-actions';
import { MailAdvancedSearch, MailDateSelect } from './components/mail-search';
import type { FeatureRoute } from '@/lib/routing';

/* ------------------------------------------------------------------ */
/* Écrans de liste génériques du courrier (propres à la feature Mails) */
/* ------------------------------------------------------------------ */
function makeMailList(columns: TableColumn<Entity>[], o: { title: string; desc?: string; create?: string; preset?: Record<string, string>; del?: boolean }) {
  return function MailList() {
    const [page, setPage] = useState(1);
    const [d, setD] = useState('');
    const params: Record<string, unknown> = { page, 'page.size': 20 };
    if (d) params['filter[name][like]'] = d;
    for (const [k, v] of Object.entries(o.preset ?? {})) params[`filter[${k}]`] = v;
    const { data, isLoading, isError } = useResourceList(api.mailsApi, 'mails', params as never);
    const destroy = useDestroy(api.mailsApi, 'mails');
    const rows = (data?.data ?? []) as Entity[];
    const m = data?.meta;
    return (
      <div className="flex h-full flex-col gap-4">
        <header className="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h1 className="text-xl font-semibold">{o.title}</h1>
            {o.desc && <p className="mt-1 text-sm text-muted-foreground">{o.desc}</p>}
          </div>
          <div className="flex gap-2">
            <input type="search" placeholder="Rechercher…" onChange={(e) => { setD(e.target.value); setPage(1); }} className="w-56 rounded border border-border bg-surface px-3 py-1.5 text-sm" />
            {o.create && <Link href={o.create} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link>}
          </div>
        </header>
        <DataTable columns={columns} rows={rows} loading={isLoading} error={isError} emptyLabel="Aucun courrier."
          actions={o.del ? (row) => (
            <button type="button" onClick={() => { if (window.confirm('Supprimer ce courrier ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>
          ) : undefined} />
        <Pagination page={page} totalPages={m?.last_page ?? 1} total={m?.total} onChange={setPage} />
      </div>
    );
  };
}

const MAIL_COLUMNS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/mails/${r.id}`} className="hover:underline">{String(r.name ?? '')}</Link> },
  { key: 'date', label: 'Date', render: (r) => (r.date ? new Date(String(r.date)).toLocaleDateString('fr-FR') : '—') },
  { key: 'mail_type', label: 'Type', render: (r) => String(r.mail_type ?? '') },
  { key: 'status', label: 'Statut', render: (r) => String(r.status ?? '') },
];

/* ------------------------------------------------------------------ */
/* Fiche détail d'un courrier                                          */
/* ------------------------------------------------------------------ */
function MailDetail({ id }: { id: string }) {
  const { data, isLoading } = useResource(api.mailsApi, 'mails', id);
  const destroy = useDestroy(api.mailsApi, 'mails');
  const router = useRouter();
  if (isLoading) return <p className="text-sm text-muted-foreground">Chargement…</p>;
  const m = data?.data ?? {};
  return (
    <div className="flex flex-col gap-4">
      <header className="flex items-center justify-between">
        <div>
          <p className="font-mono text-xs text-muted-foreground">{String(m.code ?? '')}</p>
          <h1 className="text-xl font-semibold">{String(m.name ?? '')}</h1>
        </div>
        <div className="flex gap-2">
          <Link href={`/mails/${id}/edit`} className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted">Modifier</Link>
          <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) { destroy.mutate(id); router.push('/mails'); } }} className="rounded border border-danger/40 bg-danger/10 px-3 py-1.5 text-sm text-danger">Supprimer</button>
        </div>
      </header>
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        {([
          ['Code', m.code], ['Nom', m.name], ['Date', m.date ? new Date(String(m.date)).toLocaleDateString('fr-FR') : '—'], ['Échéance', m.deadline ? new Date(String(m.deadline)).toLocaleDateString('fr-FR') : '—'], ['Type', m.mail_type], ['Statut', m.status], ['Description', m.description],
        ] as [string, unknown][]).map(([k, v]) => (
          <div key={String(k)} className="rounded border border-border bg-surface p-3">
            <p className="text-xs font-semibold uppercase text-muted-foreground">{k}</p>
            <p className="mt-1 text-sm">{String(v ?? '—')}</p>
          </div>
        ))}
      </div>
      <Attachments mailId={id} />
    </div>
  );
}

function Attachments({ mailId }: { mailId: string }) {
  const { data, isLoading } = useResourceList(api.mailAttachmentsApi, 'mail-attachments', { 'filter[mail_id]': mailId, 'page.size': 50 } as never);
  return (
    <div>
      <h2 className="mb-2 text-sm font-semibold">Pièces jointes</h2>
      <DataTable columns={[{ key: 'name', label: 'Nom' }, { key: 'mime_type', label: 'Type' }, { key: 'size', label: 'Taille' }]} rows={(data?.data ?? []) as Entity[]} loading={isLoading} emptyLabel="Aucune pièce jointe." />
    </div>
  );
}

/* ------------------------------------------------------------------ */
/* Formulaire courrier                                                 */
/* ------------------------------------------------------------------ */
function MailForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const { data } = useResource(api.mailsApi, 'mails', id);
  const create = useCreate(api.mailsApi, 'mails');
  const update = useUpdate(api.mailsApi, 'mails');
  const [v, setV] = useState<Record<string, string>>({});
  if (mode === 'edit' && data?.data && Object.keys(v).length === 0) {
    const e = data.data;
    setV({ code: String(e.code ?? ''), name: String(e.name ?? ''), description: String(e.description ?? ''), mail_type: String(e.mail_type ?? 'internal'), status: String(e.status ?? 'draft') });
  }
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (mode === 'edit' && id) await update.mutateAsync({ id, payload: v });
    else await create.mutateAsync(v);
    router.push('/mails');
  }
  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{mode === 'edit' ? 'Modifier — courrier' : 'Créer — courrier'}</h1>
        <button type="button" onClick={() => router.push('/mails')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
      </header>
      <div className="grid grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
        <Field label="Code" value={v.code} onChange={(x) => setV((p) => ({ ...p, code: x }))} />
        <Field label="Nom *" value={v.name} onChange={(x) => setV((p) => ({ ...p, name: x }))} required />
        <Field label="Description" value={v.description} onChange={(x) => setV((p) => ({ ...p, description: x }))} />
        <Field label="Date" type="date" value={v.date ?? ''} onChange={(x) => setV((p) => ({ ...p, date: x }))} />
        <Field label="Échéance" type="date" value={v.deadline ?? ''} onChange={(x) => setV((p) => ({ ...p, deadline: x }))} />
        <Field label="Type" select={[['internal', 'Interne'], ['incoming', 'Entrant'], ['outgoing', 'Sortant']]} value={v.mail_type} onChange={(x) => setV((p) => ({ ...p, mail_type: x }))} />
        <Field label="Statut" select={[['draft', 'Brouillon'], ['transmitted', 'Transmis'], ['completed', 'Terminé']]} value={v.status} onChange={(x) => setV((p) => ({ ...p, status: x }))} />
      </div>
      <footer className="flex justify-end">
        <button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button>
      </footer>
    </form>
  );
}

function Field({ label, value, onChange, type = 'text', select, required }: { label: string; value?: string; onChange: (v: string) => void; type?: string; select?: [string, string][]; required?: boolean }) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>{label} {required && <span className="text-danger">*</span>}</span>
      {select ? (
        <select value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm">
          {select.map(([val, lab]) => <option key={val} value={val}>{lab}</option>)}
        </select>
      ) : (
        <input type={type} value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
      )}
    </label>
  );
}

/* ------------------------------------------------------------------ */
/* Référentiels (listes simples)                                       */
/* ------------------------------------------------------------------ */
const REF_COLUMNS: TableColumn<Entity>[] = [
  { key: 'code', label: 'Code', render: (r) => <span className="font-mono text-xs">{String(r.code ?? '')}</span> },
  { key: 'name', label: 'Nom' },
];

function makeRefList(r: ResourceApi, key: string, title: string, createPath: string) {
  return function RefList() {
    const { data, isLoading, isError } = useResourceList(r, key, { 'page.size': 50 } as never);
    const destroy = useDestroy(r, key);
    const rows = (data?.data ?? []) as Entity[];
    return (
      <div className="flex h-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{title}</h1>
          <Link href={createPath} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link>
        </header>
        <DataTable columns={REF_COLUMNS} rows={rows} loading={isLoading} error={isError}
          actions={(row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>} />
      </div>
    );
  };
}

/* ------------------------------------------------------------------ */
/* Routes de la feature Mails                                          */
/* ------------------------------------------------------------------ */
export const routes: FeatureRoute[] = [
  { path: '/mails', List: makeMailList(MAIL_COLUMNS, { title: 'Courriers', desc: 'Tous les courriers', create: '/mails/create', del: true }), Detail: MailDetail, Form: MailForm },
  { path: '/mails/received', List: makeMailList(MAIL_COLUMNS, { title: 'Courriers reçus', preset: { mail_type: 'incoming' }, create: '/mails/received/create', del: true }), Detail: MailDetail, Form: MailForm },
  { path: '/mails/sent', List: makeMailList(MAIL_COLUMNS, { title: 'Courriers envoyés', preset: { mail_type: 'outgoing' }, create: '/mails/sent/create', del: true }), Detail: MailDetail, Form: MailForm },
  { path: '/mails/returned', List: makeMailList(MAIL_COLUMNS, { title: 'Courriers retournés', preset: { status: 'completed' }, del: true }), Detail: MailDetail },
  { path: '/mails/to-return', List: makeMailList(MAIL_COLUMNS, { title: 'Courriers à retourner', preset: { status: 'transmitted' }, del: true }), Detail: MailDetail },
  { path: '/mails/external/send', List: makeMailList(MAIL_COLUMNS, { title: 'Courriers sortants externes', preset: { mail_type: 'outgoing' }, create: '/mails/external/send/create', del: true }), Detail: MailDetail, Form: MailForm },
  { path: '/mails/external/receive', List: makeMailList(MAIL_COLUMNS, { title: 'Courriers entrants externes', preset: { mail_type: 'incoming' }, create: '/mails/external/receive/create', del: true }), Detail: MailDetail, Form: MailForm },
  { path: '/mails/archived', List: makeMailList(MAIL_COLUMNS, { title: 'Courriers archivés', preset: { is_archived: '1' }, del: true }), Detail: MailDetail },

  { path: '/mails/batches', List: makeRefList(api.batchesApi, 'batches', 'Parapheurs', '/mails/batches/create'), Form: () => <FormForRef title="Créer un parapheur" back="/mails/batches" onSave={(p) => api.batchesApi.create(p)} fields={[{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom' }]} /> },
  { path: '/mails/containers', List: makeRefList(api.mailContainersApi, 'mail-containers', "Boîtes de courrier", '/mails/containers/create'), Form: () => <FormForRef title="Créer une boîte" back="/mails/containers" onSave={(p) => api.mailContainersApi.create(p)} fields={[{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom' }]} /> },
  { path: '/mails/typologies', aliases: ['/settings/mail-typologies'], List: makeRefList(api.mailTypologiesApi, 'mail-typologies', 'Typologies de courrier', '/mails/typologies/create'), Form: () => <FormForRef title="Créer une typologie" back="/mails/typologies" onSave={(p) => api.mailTypologiesApi.create(p)} fields={[{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom' }]} /> },
  { path: '/settings/mail-actions', List: makeRefList(api.mailActionsApi, 'mail-actions', 'Actions de courrier', '/settings/mail-actions/create'), Form: () => <FormForRef title="Créer une action" back="/settings/mail-actions" onSave={(p) => api.mailActionsApi.create(p)} fields={[{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom' }]} /> },
  { path: '/settings/mail-priorities', List: makeRefList(api.mailPrioritiesApi, 'mail-priorities', 'Priorités de courrier', '/settings/mail-priorities/create'), Form: () => <FormForRef title="Créer une priorité" back="/settings/mail-priorities" onSave={(p) => api.mailPrioritiesApi.create(p)} fields={[{ name: 'code', label: 'Code' }, { name: 'name', label: 'Nom' }]} /> },
  { path: '/mails/attachments', List: makeRefList(api.mailAttachmentsApi, 'mail-attachments', 'Pièces jointes', '/mails/attachments/create') },
  { path: '/settings/batch-transactions', List: makeRefList(api.batchTransactionsApi, 'batch-transactions', 'Transactions de lot', '/settings/batch-transactions/create') },

  // Écrans spécialisés
  { path: '/mails/batches/sign', List: () => <ParapheurActions action="sign" /> },
  { path: '/mails/batches/send', List: () => <ParapheurActions action="send" /> },
  { path: '/mails/batches/receive', List: () => <ParapheurActions action="receive" /> },
  { path: '/mails/advanced', List: MailAdvancedSearch },
  { path: '/mails/select/date', List: MailDateSelect },
];

/** Formulaire référentiel (propre à la feature Mails). */
function FormForRef({ title, back, onSave, fields }: { title: string; back: string; onSave: (p: Record<string, unknown>) => Promise<unknown>; fields: { name: string; label: string }[] }) {
  const router = useRouter();
  const [v, setV] = useState<Record<string, string>>({});
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    await onSave(v);
    router.push(back);
  }
  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{title}</h1>
        <button type="button" onClick={() => router.push(back)} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
      </header>
      <div className="grid max-w-2xl grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2">
        {fields.map((f) => <Field key={f.name} label={f.label} value={v[f.name] ?? ''} onChange={(x) => setV((p) => ({ ...p, [f.name]: x }))} />)}
      </div>
      <footer className="flex justify-end">
        <button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button>
      </footer>
    </form>
  );
}
