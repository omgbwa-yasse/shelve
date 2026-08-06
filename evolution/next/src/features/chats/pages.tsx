'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useResource, useCreate } from '@/lib/api/hooks';
import { DataTable } from '@/components/ui/table';
import * as api from './services/chat.service';
import type { FeatureRoute } from '@/lib/routing';

const COLS: TableColumn<Entity>[] = [
  { key: 'name', label: 'Nom', render: (r) => <Link href={`/chats/${r.id}`} className="hover:underline">{String(r.name ?? '')}</Link> },
  { key: 'created_at', label: 'Créée le', render: (r) => (r.created_at ? new Date(String(r.created_at)).toLocaleDateString('fr-FR') : '—') },
];

export function ChatList() {
  const { data, isLoading, isError } = useResourceList(api.chatConversationsApi, 'chat-conversations', { 'page.size': 50 } as never);
  const destroy = useDestroy(api.chatConversationsApi, 'chat-conversations');
  const rows = (data?.data ?? []) as Entity[];
  return (
    <div className="flex h-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">Conversations</h1>
        <Link href="/chats/create" className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link>
      </header>
      <DataTable columns={COLS} rows={rows} loading={isLoading} error={isError}
        actions={(row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>} />
    </div>
  );
}

export function ChatForm({ mode, id }: { mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const { data } = useResource(api.chatConversationsApi, 'chat-conversations', id);
  const create = useCreate(api.chatConversationsApi, 'chat-conversations');
  const [v, setV] = useState<Record<string, string>>({});
  if (mode === 'edit' && data?.data && Object.keys(v).length === 0) setV({ name: String(data.data.name ?? '') });
  async function submit(e: React.FormEvent) {
    e.preventDefault();
    if (mode === 'edit' && id) await api.chatConversationsApi.update(id, v);
    else await create.mutateAsync(v);
    router.push('/chats');
  }
  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">{mode === 'edit' ? 'Modifier — conversation' : 'Nouvelle conversation'}</h1>
        <button type="button" onClick={() => router.push('/chats')} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
      </header>
      <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4">
        <label className="flex flex-col gap-1 text-sm">
          <span>Nom *</span>
          <input value={v.name ?? ''} onChange={(e) => setV((p) => ({ ...p, name: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
        </label>
      </div>
      <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
    </form>
  );
}

export const routes: FeatureRoute[] = [
  { path: '/chats', List: ChatList, Form: ChatForm },
];
