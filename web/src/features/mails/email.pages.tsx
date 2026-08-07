'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter, useSearchParams } from 'next/navigation';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useResource, useCreate, useUpdate, useDestroy } from '@/lib/api/hooks';
import { apiFetch } from '@/lib/api/client';
import { DataTable, Pagination } from '@/components/ui/table';
import * as api from './services/email.service';
import { EMAIL_ENCRYPTION_LABELS } from './services/email.service';
import type { FeatureRoute } from '@/lib/routing';

/* ---------------------------------------------------------------------------
 * Nav commune (dossiers + compte + composer) — affichée en tête de chaque écran.
 * ------------------------------------------------------------------------- */
function EmailNav({ folder }: { folder: 'inbox' | 'sent' | null }) {
  return (
    <nav className="flex flex-wrap items-center justify-between gap-2 border-b border-border pb-3">
      <div className="flex gap-1">
        <Link href="/mails/email" className={`rounded px-3 py-1.5 text-sm ${folder === 'inbox' ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`}>
          Réception
        </Link>
        <Link href="/mails/email/sent" className={`rounded px-3 py-1.5 text-sm ${folder === 'sent' ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}`}>
          Envoyés
        </Link>
        <Link href="/mails/email/tags" className="rounded px-3 py-1.5 text-sm hover:bg-muted">Étiquettes</Link>
        <Link href="/mails/email/accounts" className="rounded px-3 py-1.5 text-sm hover:bg-muted">Paramètres</Link>
      </div>
      <Link href="/mails/email/compose" className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Composer</Link>
    </nav>
  );
}

function labelOf(v: unknown, field: string): string {
  const o = v as Entity | undefined | null;
  if (!o) return '—';
  return String(o[field] ?? o.name ?? '—');
}

/**
 * Le backend refuse déjà tout appel `/api/v1/email-messages|email-tags` tant
 * que le module est désactivé (middleware `email.enabled`) — ce hook sert
 * uniquement à l'UX : afficher un message clair plutôt que de laisser les
 * écrans tenter des appels voués à un 403.
 */
function useEmailModuleEnabled() {
  const { data, isLoading } = useQuery({
    queryKey: ['email-status'],
    queryFn: () => apiFetch<{ data: { enabled: boolean } }>('/api/v1/email'),
    staleTime: 60_000,
  });
  return { enabled: data?.data.enabled ?? false, isLoading };
}

function EmailModuleGate({ children }: { children: React.ReactNode }) {
  const { enabled, isLoading } = useEmailModuleEnabled();

  if (isLoading) return <p className="text-sm text-muted-foreground">Chargement…</p>;

  if (!enabled) {
    return (
      <div className="flex h-full flex-col gap-4">
        <h1 className="text-xl font-semibold">Messagerie</h1>
        <div className="rounded border border-border bg-surface p-6 text-center">
          <p className="text-sm text-muted-foreground">
            Le module Email n&apos;est pas activé pour votre organisation.
          </p>
          <Link href="/mails/email/accounts" className="mt-3 inline-block rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">
            Aller aux paramètres
          </Link>
        </div>
      </div>
    );
  }

  return <>{children}</>;
}

/* ---------------------------------------------------------------------------
 * Liste des messages (réception / envoyés) — factorisée sur le dossier IMAP.
 * ------------------------------------------------------------------------- */
function makeMessageList(folder: 'INBOX' | 'Sent', navFolder: 'inbox' | 'sent', title: string) {
  return function MessageList() {
    const [page, setPage] = useState(1);
    const [q, setQ] = useState('');
    const { data, isLoading, isError } = useQuery({
      queryKey: ['email-messages', folder, page, q],
      queryFn: () => api.getEmailMessages({ 'filter[folder]': folder, page, 'page.size': 25, ...(q ? { q } : {}) }),
    });
    const rows = (data?.data ?? []) as Entity[];
    const m = data?.meta;

    return (
      <div className="flex h-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{title}</h1>
        </header>
        <EmailNav folder={navFolder} />
        <input
          type="search" placeholder="Rechercher un sujet…" value={q}
          onChange={(e) => { setQ(e.target.value); setPage(1); }}
          className="w-72 rounded border border-border bg-surface px-3 py-1.5 text-sm"
        />
        <DataTable
          columns={[
            {
              key: 'from', label: 'De/À', render: (r) => (
                <Link href={`/mails/email/${r.id}`} className={`hover:underline ${!r.is_read ? 'font-semibold' : ''}`}>
                  {String(r.from_name || r.from_address || '—')}
                </Link>
              ),
            },
            {
              key: 'subject', label: 'Sujet', render: (r) => (
                <span className={!r.is_read ? 'font-semibold' : ''}>
                  {String(r.subject || '(Sans sujet)')}
                  {r.is_flagged ? ' ⭐' : ''}
                  {r.has_attachments ? ' 📎' : ''}
                </span>
              ),
            },
            { key: 'sent_at', label: 'Date', render: (r) => (r.sent_at ? new Date(String(r.sent_at)).toLocaleString('fr-FR') : '—') },
          ]}
          rows={rows}
          loading={isLoading}
          error={isError}
          emptyLabel="Aucun message."
          actions={(row) => <Link href={`/mails/email/${row.id}`} className="rounded border border-border px-2 py-1 text-xs hover:bg-muted">Ouvrir</Link>}
        />
        <Pagination page={page} totalPages={m?.last_page ?? 1} total={m?.total} onChange={setPage} />
      </div>
    );
  };
}

/* ---------------------------------------------------------------------------
 * Détail d'un message — corps HTML isolé dans un iframe sandboxée (jamais en
 * dangerouslySetInnerHTML : un email est un contenu externe non fiable, voir
 * resources/views/mails/email/show.blade.php pour l'équivalent côté web).
 * ------------------------------------------------------------------------- */
function EmailMessageDetail({ id }: { id: string }) {
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({ queryKey: ['email-message', id], queryFn: () => api.getEmailMessage(id) });
  const { data: tagsData } = useQuery({ queryKey: ['email-tags'], queryFn: api.getEmailTags });
  const [tagToAdd, setTagToAdd] = useState('');

  if (isLoading) return <p className="text-sm text-muted-foreground">Chargement…</p>;
  const message = (data?.data ?? {}) as Entity;
  const allTags = (tagsData?.data ?? []) as Entity[];
  const messageTags = (message.tags ?? []) as Entity[];

  async function toggleFlag() {
    await api.toggleEmailFlag(message);
    queryClient.invalidateQueries({ queryKey: ['email-message', id] });
  }

  async function addTag(e: React.FormEvent) {
    e.preventDefault();
    if (!tagToAdd) return;
    await api.attachEmailTag(id, tagToAdd);
    setTagToAdd('');
    queryClient.invalidateQueries({ queryKey: ['email-message', id] });
  }

  async function removeTag(tagId: string | number) {
    await api.detachEmailTag(id, tagId);
    queryClient.invalidateQueries({ queryKey: ['email-message', id] });
  }

  const toAddresses = ((message.to ?? []) as { mail: string }[]).map((a) => a.mail).join(', ');

  return (
    <div className="flex flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-lg font-semibold">{String(message.subject || '(Sans sujet)')}</h1>
        <div className="flex gap-2">
          <Link href={`/mails/email/compose?reply_to=${id}`} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">Répondre</Link>
          <button type="button" onClick={toggleFlag} className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted">
            {message.is_flagged ? '★ Favori' : '☆ Favori'}
          </button>
          <Link href={message.folder === 'Sent' ? '/mails/email/sent' : '/mails/email'} className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted">Retour</Link>
        </div>
      </header>

      <div className="rounded border border-border bg-surface p-4 text-sm">
        <p><span className="text-muted-foreground">De :</span> {String(message.from_name || '')} &lt;{String(message.from_address || '')}&gt;</p>
        <p><span className="text-muted-foreground">À :</span> {toAddresses || '—'}</p>
        <p><span className="text-muted-foreground">Date :</span> {message.sent_at ? new Date(String(message.sent_at)).toLocaleString('fr-FR') : '—'}</p>
      </div>

      <div className="rounded border border-border bg-surface p-4">
        {message.body_html ? (
          <iframe
            title="Contenu du message"
            srcDoc={String(message.body_html)}
            sandbox="allow-same-origin"
            className="w-full border-0"
            onLoad={(e) => {
              const frame = e.currentTarget;
              try {
                frame.style.height = `${(frame.contentWindow?.document.body?.scrollHeight ?? 300) + 20}px`;
              } catch {
                frame.style.height = '300px';
              }
            }}
          />
        ) : (
          <pre className="whitespace-pre-wrap text-sm">{String(message.body_text || '')}</pre>
        )}
      </div>

      {(message.attachments as Entity[] | undefined)?.length ? (
        <div className="rounded border border-border bg-surface p-4">
          <h2 className="mb-2 text-sm font-semibold">Pièces jointes</h2>
          <ul className="flex flex-col gap-1 text-sm">
            {(message.attachments as Entity[]).map((a) => (
              <li key={String(a.id)}>
                <a href={String(a.download_url)} className="text-primary hover:underline">{String(a.filename)}</a>
                <span className="ml-2 text-xs text-muted-foreground">({Math.round(Number(a.size ?? 0) / 1024)} Ko)</span>
              </li>
            ))}
          </ul>
        </div>
      ) : null}

      <div className="rounded border border-border bg-surface p-4">
        <h2 className="mb-2 text-sm font-semibold">Étiquettes</h2>
        <div className="mb-2 flex flex-wrap gap-2">
          {messageTags.map((t) => (
            <span key={String(t.id)} className="flex items-center gap-1 rounded-full px-2 py-0.5 text-xs text-white" style={{ backgroundColor: String(t.color) }}>
              {String(t.name)}
              <button type="button" onClick={() => removeTag(t.id as string | number)} className="ml-1">&times;</button>
            </span>
          ))}
        </div>
        <form onSubmit={addTag} className="flex gap-2">
          <select value={tagToAdd} onChange={(e) => setTagToAdd(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm">
            <option value="">Ajouter une étiquette…</option>
            {allTags.filter((t) => !messageTags.some((mt) => mt.id === t.id)).map((t) => (
              <option key={String(t.id)} value={String(t.id)}>{String(t.name)}</option>
            ))}
          </select>
          <button type="submit" className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted">Ajouter</button>
        </form>
      </div>
    </div>
  );
}

/* ---------------------------------------------------------------------------
 * Composer / répondre.
 * ------------------------------------------------------------------------- */
function EmailComposeForm() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const replyToId = searchParams.get('reply_to');
  const { data: replyToData } = useQuery({
    queryKey: ['email-message', replyToId],
    queryFn: () => api.getEmailMessage(replyToId as string),
    enabled: !!replyToId,
  });
  const { data: accountsData } = useQuery({ queryKey: ['email-accounts-options'], queryFn: () => api.emailAccountsApi.list({ 'page.size': 50 } as never) });
  const accounts = (accountsData?.data ?? []) as Entity[];
  const replyTo = replyToData?.data as Entity | undefined;

  const [accountId, setAccountId] = useState('');
  const [to, setTo] = useState('');
  const [cc, setCc] = useState('');
  const [bcc, setBcc] = useState('');
  const [subject, setSubject] = useState('');
  const [body, setBody] = useState('');
  const [sending, setSending] = useState(false);
  const [initialised, setInitialised] = useState(false);

  if (replyTo && !initialised) {
    setTo(String(replyTo.from_address ?? ''));
    setSubject(`Re: ${String(replyTo.subject ?? '')}`);
    setInitialised(true);
  }
  if (accounts.length > 0 && !accountId && accounts[0]) {
    setAccountId(String(accounts[0].id));
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setSending(true);
    try {
      await api.sendEmailMessage({
        email_account_id: accountId,
        to: to.split(',').map((s) => s.trim()).filter(Boolean),
        cc: cc.split(',').map((s) => s.trim()).filter(Boolean),
        bcc: bcc.split(',').map((s) => s.trim()).filter(Boolean),
        subject,
        body_html: body,
        in_reply_to: replyTo ? String(replyTo.message_id ?? '') : null,
      });
      router.push('/mails/email/sent');
    } finally {
      setSending(false);
    }
  }

  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{replyTo ? 'Répondre' : 'Nouveau message'}</h1>
        <button type="button" onClick={() => router.push('/mails/email')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
      </header>

      {accounts.length === 0 ? (
        <p className="text-sm text-muted-foreground">
          Aucun compte de messagerie actif. <Link href="/mails/email/accounts/create" className="text-primary hover:underline">Configurer un compte</Link>.
        </p>
      ) : (
        <div className="flex flex-col gap-4 rounded border border-border bg-surface p-4">
          {accounts.length > 1 && (
            <label className="flex flex-col gap-1 text-sm">
              <span>Compte expéditeur</span>
              <select value={accountId} onChange={(e) => setAccountId(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5">
                {accounts.map((a) => <option key={String(a.id)} value={String(a.id)}>{String(a.name)} &lt;{String(a.email_address)}&gt;</option>)}
              </select>
            </label>
          )}
          <label className="flex flex-col gap-1 text-sm">
            <span>À *</span>
            <input value={to} onChange={(e) => setTo(e.target.value)} required placeholder="adresse@exemple.com, autre@exemple.com" className="rounded border border-border bg-background px-2 py-1.5" />
          </label>
          <div className="grid grid-cols-2 gap-4">
            <label className="flex flex-col gap-1 text-sm">
              <span>Cc</span>
              <input value={cc} onChange={(e) => setCc(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5" />
            </label>
            <label className="flex flex-col gap-1 text-sm">
              <span>Cci</span>
              <input value={bcc} onChange={(e) => setBcc(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5" />
            </label>
          </div>
          <label className="flex flex-col gap-1 text-sm">
            <span>Sujet *</span>
            <input value={subject} onChange={(e) => setSubject(e.target.value)} required className="rounded border border-border bg-background px-2 py-1.5" />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span>Message *</span>
            <textarea value={body} onChange={(e) => setBody(e.target.value)} required rows={12} className="rounded border border-border bg-background px-2 py-1.5" />
          </label>
          <div>
            <button type="submit" disabled={sending} className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground disabled:opacity-50">
              {sending ? 'Envoi…' : 'Envoyer'}
            </button>
          </div>
        </div>
      )}
    </form>
  );
}

/* ---------------------------------------------------------------------------
 * Étiquettes.
 * ------------------------------------------------------------------------- */
function EmailTagsList() {
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({ queryKey: ['email-tags-full'], queryFn: api.getEmailTags });
  const tags = (data?.data ?? []) as Entity[];
  const [name, setName] = useState('');
  const [color, setColor] = useState('#6b7280');

  async function create(e: React.FormEvent) {
    e.preventDefault();
    await api.emailTagsApi.create({ name, color });
    setName('');
    queryClient.invalidateQueries({ queryKey: ['email-tags-full'] });
  }

  async function remove(id: string | number) {
    if (!window.confirm('Supprimer cette étiquette ?')) return;
    await api.emailTagsApi.destroy(id);
    queryClient.invalidateQueries({ queryKey: ['email-tags-full'] });
  }

  return (
    <div className="flex h-full flex-col gap-4">
      <h1 className="text-xl font-semibold">Messagerie</h1>
      <EmailNav folder={null} />
      <form onSubmit={create} className="flex items-end gap-2 rounded border border-border bg-surface p-4">
        <label className="flex flex-col gap-1 text-sm">
          <span>Nom</span>
          <input value={name} onChange={(e) => setName(e.target.value)} required className="rounded border border-border bg-background px-2 py-1.5" />
        </label>
        <label className="flex flex-col gap-1 text-sm">
          <span>Couleur</span>
          <input type="color" value={color} onChange={(e) => setColor(e.target.value)} className="h-9 w-16 rounded border border-border bg-background" />
        </label>
        <button type="submit" className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">Ajouter</button>
      </form>
      <DataTable
        columns={[
          { key: 'name', label: 'Étiquette', render: (r) => <span className="rounded-full px-2 py-0.5 text-xs text-white" style={{ backgroundColor: String(r.color) }}>{String(r.name)}</span> },
          { key: 'messages_count', label: 'Messages', render: (r) => String(r.messages_count ?? 0) },
        ]}
        rows={tags}
        loading={isLoading}
        emptyLabel="Aucune étiquette."
        actions={(row) => <button type="button" onClick={() => remove(row.id as string | number)} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>}
      />
    </div>
  );
}

/* ---------------------------------------------------------------------------
 * Comptes de messagerie (settings).
 * ------------------------------------------------------------------------- */
function EmailAccountsList() {
  const queryClient = useQueryClient();
  const { data, isLoading } = useResourceList(api.emailAccountsApi, 'email-accounts', { 'page.size': 50 } as never);
  const destroy = useDestroy(api.emailAccountsApi, 'email-accounts');
  const rows = (data?.data ?? []) as Entity[];
  const { enabled: moduleEnabled, isLoading: moduleLoading } = useEmailModuleEnabled();

  async function sync(id: string | number) {
    await api.syncEmailAccount(id);
    window.alert('Synchronisation lancée en arrière-plan.');
  }

  async function toggleActive(id: string | number) {
    await api.toggleEmailAccountActive(id);
    queryClient.invalidateQueries({ queryKey: ['email-accounts'] });
  }

  async function toggleModule() {
    await api.toggleEmailModule();
    queryClient.invalidateQueries({ queryKey: ['email-status'] });
  }

  return (
    <div className="flex h-full flex-col gap-4">
      <h1 className="text-xl font-semibold">Messagerie</h1>
      <EmailNav folder={null} />

      <div className="flex items-center justify-between rounded border border-border bg-surface p-4">
        <div>
          <h2 className="text-sm font-semibold">Email</h2>
          <p className="mt-1 text-xs text-muted-foreground">
            Tant qu&apos;il est désactivé, la section « Email » n&apos;apparaît pas dans le menu — seule cette page reste accessible.
          </p>
        </div>
        <button
          type="button" onClick={toggleModule} disabled={moduleLoading}
          className={`rounded px-3 py-1.5 text-sm ${moduleEnabled ? 'bg-success text-white' : 'border border-border hover:bg-muted'}`}
        >
          {moduleEnabled ? 'Activé — désactiver' : 'Désactivé — activer'}
        </button>
      </div>

      <header className="flex items-center justify-between">
        <h2 className="text-lg font-semibold">Comptes de messagerie</h2>
        <Link href="/mails/email/accounts/create" className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau compte</Link>
      </header>
      <DataTable
        columns={[
          { key: 'name', label: 'Nom' },
          { key: 'email_address', label: 'Adresse' },
          { key: 'imap_host', label: 'IMAP', render: (r) => <span className="font-mono text-xs">{String(r.imap_host)}:{String(r.imap_port)}</span> },
          {
            key: 'is_active', label: 'Statut', render: (r) => (
              <button
                type="button" onClick={() => toggleActive(r.id as string | number)}
                className={`rounded px-2 py-0.5 text-xs ${r.is_active ? 'bg-success/20 text-success' : 'bg-muted text-muted-foreground'}`}
                title={r.is_active ? 'Cliquer pour désactiver' : 'Cliquer pour activer'}
              >
                {r.is_active ? 'Actif' : 'Inactif'}
              </button>
            ),
          },
          { key: 'last_synced_at', label: 'Dernière synchro', render: (r) => (r.last_synced_at ? new Date(String(r.last_synced_at)).toLocaleString('fr-FR') : 'Jamais') },
        ]}
        rows={rows}
        loading={isLoading}
        emptyLabel="Aucun compte configuré."
        actions={(row) => (
          <div className="flex justify-end gap-1">
            <button type="button" onClick={() => sync(row.id as string | number)} className="rounded border border-border px-2 py-1 text-xs hover:bg-muted">Synchroniser</button>
            <Link href={`/mails/email/accounts/${row.id}/edit`} className="rounded border border-border px-2 py-1 text-xs hover:bg-muted">Modifier</Link>
            <button type="button" onClick={() => { if (window.confirm('Supprimer ce compte ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>
          </div>
        )}
      />
    </div>
  );
}

function AccountField({ label, value, onChange, type = 'text', options, required }: {
  label: string; value?: string; onChange: (v: string) => void; type?: string;
  options?: { value: string; label: string }[]; required?: boolean;
}) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>{label}</span>
      {options ? (
        <select value={value ?? ''} onChange={(e) => onChange(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5">
          {options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
        </select>
      ) : (
        <input type={type} value={value ?? ''} onChange={(e) => onChange(e.target.value)} required={required} className="rounded border border-border bg-background px-2 py-1.5" />
      )}
    </label>
  );
}

function EmailAccountForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const { data } = useResource(api.emailAccountsApi, 'email-account', id);
  const create = useCreate(api.emailAccountsApi, 'email-accounts');
  const update = useUpdate(api.emailAccountsApi, 'email-accounts');
  const [v, setV] = useState<Record<string, string>>({ imap_port: '993', imap_encryption: 'ssl', smtp_port: '587', smtp_encryption: 'tls' });
  const [loaded, setLoaded] = useState(false);

  if (mode === 'edit' && data?.data && !loaded) {
    const e = data.data;
    setV({
      name: String(e.name ?? ''), email_address: String(e.email_address ?? ''),
      imap_host: String(e.imap_host ?? ''), imap_port: String(e.imap_port ?? '993'), imap_encryption: String(e.imap_encryption ?? 'ssl'), imap_username: String(e.imap_username ?? ''),
      smtp_host: String(e.smtp_host ?? ''), smtp_port: String(e.smtp_port ?? '587'), smtp_encryption: String(e.smtp_encryption ?? 'tls'), smtp_username: String(e.smtp_username ?? ''),
      default_from_name: String(e.default_from_name ?? ''),
    });
    setLoaded(true);
  }

  function setField(name: string, value: string) {
    setV((p) => ({ ...p, [name]: value }));
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    const payload: Record<string, unknown> = { ...v };
    for (const k of Object.keys(payload)) if (payload[k] === '') delete payload[k];

    if (mode === 'edit' && id) await update.mutateAsync({ id, payload });
    else await create.mutateAsync(payload);

    router.push('/mails/email/accounts');
  }

  const encryptionOptions = Object.entries(EMAIL_ENCRYPTION_LABELS).map(([value, label]) => ({ value, label }));

  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4 pb-8">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{mode === 'edit' ? 'Modifier le compte' : 'Nouveau compte de messagerie'}</h1>
        <button type="button" onClick={() => router.push('/mails/email/accounts')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
      </header>

      <fieldset className="grid grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2">
        <legend className="px-1 text-sm font-semibold">Identification</legend>
        <AccountField label="Nom du compte *" value={v.name} onChange={(x) => setField('name', x)} required />
        <AccountField label="Adresse email *" value={v.email_address} onChange={(x) => setField('email_address', x)} type="email" required />
        <AccountField label="Nom d'expéditeur affiché" value={v.default_from_name} onChange={(x) => setField('default_from_name', x)} />
      </fieldset>

      <fieldset className="grid grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2">
        <legend className="px-1 text-sm font-semibold">Réception (IMAP)</legend>
        <AccountField label="Serveur IMAP *" value={v.imap_host} onChange={(x) => setField('imap_host', x)} required />
        <AccountField label="Port *" value={v.imap_port} onChange={(x) => setField('imap_port', x)} type="number" required />
        <AccountField label="Chiffrement" value={v.imap_encryption} onChange={(x) => setField('imap_encryption', x)} options={encryptionOptions} />
        <AccountField label="Identifiant *" value={v.imap_username} onChange={(x) => setField('imap_username', x)} required />
        <AccountField label={mode === 'edit' ? 'Nouveau mot de passe' : 'Mot de passe *'} value={v.imap_password} onChange={(x) => setField('imap_password', x)} type="password" required={mode === 'create'} />
      </fieldset>

      <fieldset className="grid grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2">
        <legend className="px-1 text-sm font-semibold">Envoi (SMTP)</legend>
        <AccountField label="Serveur SMTP *" value={v.smtp_host} onChange={(x) => setField('smtp_host', x)} required />
        <AccountField label="Port *" value={v.smtp_port} onChange={(x) => setField('smtp_port', x)} type="number" required />
        <AccountField label="Chiffrement" value={v.smtp_encryption} onChange={(x) => setField('smtp_encryption', x)} options={encryptionOptions} />
        <AccountField label="Identifiant *" value={v.smtp_username} onChange={(x) => setField('smtp_username', x)} required />
        <AccountField label={mode === 'edit' ? 'Nouveau mot de passe' : 'Mot de passe *'} value={v.smtp_password} onChange={(x) => setField('smtp_password', x)} type="password" required={mode === 'create'} />
      </fieldset>

      <footer className="flex justify-end">
        <button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button>
      </footer>
    </form>
  );
}

const InboxList = makeMessageList('INBOX', 'inbox', 'Boîte de réception');
const SentList = makeMessageList('Sent', 'sent', 'Messages envoyés');

export const emailRoutes: FeatureRoute[] = [
  { path: '/mails/email', List: () => <EmailModuleGate><InboxList /></EmailModuleGate>, Detail: (p) => <EmailModuleGate><EmailMessageDetail {...p} /></EmailModuleGate> },
  { path: '/mails/email/sent', List: () => <EmailModuleGate><SentList /></EmailModuleGate> },
  { path: '/mails/email/compose', List: () => <EmailModuleGate><EmailComposeForm /></EmailModuleGate> },
  { path: '/mails/email/tags', List: () => <EmailModuleGate><EmailTagsList /></EmailModuleGate> },
  // Comptes : jamais gaté par EmailModuleGate — c'est ici que le module s'active.
  { path: '/mails/email/accounts', List: EmailAccountsList, Form: EmailAccountForm },
];
