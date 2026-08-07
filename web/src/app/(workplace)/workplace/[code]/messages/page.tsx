'use client';

import { useEffect, useRef, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useSearchParams } from 'next/navigation';
import { useWorkplace } from '@/features/workplaces/context';
import {
  listConversations,
  getConversation,
  sendMessage,
  createConversation,
} from '@/features/workplaces/services/workplace.service';
import { Icon } from '@/components/icons';
import { formatDateTime } from '@/utils/format-date';
import type { WorkplaceConversation, WorkplaceMessage } from '@/features/workplaces/types';

const TYPE_META: Record<string, { icon: 'users' | 'messageCircle' | 'send'; className: string; label: string }> = {
  group: { icon: 'users', className: 'text-primary', label: 'Groupes' },
  channel: { icon: 'messageCircle', className: 'text-amber-500', label: 'Canaux de diffusion' },
  private: { icon: 'send', className: 'text-green-600', label: 'Messages privés' },
};

/**
 * Chats du workplace — reproduit `workplaces/messages/index.blade.php` :
 * liste des conversations (groupes/canaux/privés), fil de discussion, envoi et
 * création de conversation.
 */
export default function WorkplaceMessagesPage() {
  const { workplace } = useWorkplace();
  const queryClient = useQueryClient();
  const searchParams = useSearchParams();
  const [selectedId, setSelectedId] = useState<string | null>(searchParams.get('conv'));

  const workplaceId = workplace?.id;
  const members = workplace?.members ?? [];

  const { data: conversationsData, isLoading: listLoading } = useQuery({
    queryKey: ['workplace-conversations', workplaceId],
    queryFn: () => listConversations(workplaceId as number),
    enabled: workplaceId != null,
  });
  const conversations = conversationsData?.data ?? [];

  const { data: selectedData, isLoading: threadLoading } = useQuery({
    queryKey: ['conversation', selectedId],
    queryFn: () => getConversation(selectedId as string),
    enabled: selectedId != null,
  });
  const selected = selectedData?.data ?? null;

  // Garde le fil descendu sur les nouveaux messages.
  const threadRef = useRef<HTMLDivElement>(null);
  useEffect(() => {
    if (threadRef.current) threadRef.current.scrollTop = threadRef.current.scrollHeight;
  }, [selected?.messages?.length]);

  const invalidate = () => {
    queryClient.invalidateQueries({ queryKey: ['workplace-conversations', workplaceId] });
  };

  const send = useMutation({
    mutationFn: ({ conversationId, content }: { conversationId: string; content: string }) =>
      sendMessage(conversationId, content),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['conversation', selectedId] });
      invalidate();
    },
  });

  return (
    <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
      {/* ===================== LISTE ===================== */}
      <aside className="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
        <header className="flex items-center justify-between border-b border-border px-4 py-2.5">
          <span className="flex items-center gap-2 text-sm font-semibold">
            <Icon name="messageCircle" className="h-4 w-4 text-primary" />
            Messages
          </span>
        </header>
        <div className="max-h-[68vh] overflow-y-auto">
          {(['group', 'channel', 'private'] as const).map((type) => {
            const meta = TYPE_META[type]!;
            const list = conversations.filter((c) => c.type === type);
            return (
              <div key={type}>
                <div className="flex items-center gap-1.5 bg-muted px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide text-muted-foreground">
                  <Icon name={meta.icon} className="h-3 w-3" />
                  {meta.label}
                </div>
                {list.length === 0 ? (
                  <p className="px-3 py-2 text-xs text-muted-foreground">Aucun</p>
                ) : (
                  list.map((c) => (
                    <button
                      key={c.id}
                      type="button"
                      onClick={() => setSelectedId(String(c.id))}
                      className={`flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-muted ${selectedId === String(c.id) ? 'bg-primary/10 text-primary' : ''}`}
                    >
                      <Icon name={meta.icon} className={`h-4 w-4 shrink-0 ${meta.className}`} />
                      <span className="truncate">{conversationTitle(c, members.length)}</span>
                    </button>
                  ))
                )}
              </div>
            );
          })}
          {listLoading && <p className="px-3 py-3 text-xs text-muted-foreground">Chargement…</p>}
        </div>
        <footer className="border-t border-border p-3">
          <NewConversationForm
            members={members}
            workplaceId={workplaceId}
            onCreated={(id) => setSelectedId(String(id))}
          />
        </footer>
      </aside>

      {/* ===================== FIL ===================== */}
      <div className="lg:col-span-2">
        {selected && selectedId ? (
          <section className="flex h-full flex-col overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
            <header className="flex items-center justify-between border-b border-border px-4 py-2.5">
              <div>
                <h4 className="flex items-center gap-2 text-sm font-semibold">
                  <Icon name={TYPE_META[selected.type]?.icon ?? 'users'} className="h-4 w-4" />
                  {conversationTitle(selected, members.length)}
                </h4>
                <p className="text-xs text-muted-foreground">
                  {selected.participants?.length ?? 0} membre(s)
                  {selected.description ? ` — ${selected.description}` : ''}
                </p>
              </div>
            </header>
            <div ref={threadRef} className="flex max-h-[52vh] flex-col gap-3 overflow-y-auto p-4">
              {threadLoading ? (
                <p className="py-8 text-center text-sm text-muted-foreground">Chargement…</p>
              ) : (selected.messages ?? []).length === 0 ? (
                <p className="py-8 text-center text-sm text-muted-foreground">Aucun message. Lancez la conversation !</p>
              ) : (
                (selected.messages ?? []).map((m) => <MessageBubble key={m.id} message={m} />)
              )}
            </div>
            <form
              onSubmit={(e) => {
                e.preventDefault();
                const input = (e.currentTarget.elements.namedItem('content') as HTMLTextAreaElement);
                const content = input.value.trim();
                if (!content) return;
                send.mutate({ conversationId: selectedId, content });
                input.value = '';
              }}
              className="flex gap-2 border-t border-border p-3"
            >
              <textarea
                name="content"
                rows={2}
                placeholder="Écrire un message…"
                className="flex-1 resize-none rounded border border-border bg-background px-2 py-1.5 text-sm"
              />
              <button
                type="submit"
                disabled={send.isPending}
                className="flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-60"
              >
                <Icon name="send" className="h-4 w-4" />
                Envoyer
              </button>
            </form>
          </section>
        ) : (
          <div className="flex h-full min-h-[60vh] flex-col items-center justify-center gap-3 rounded-xl border border-border bg-surface text-center shadow-sm">
            <Icon name="messageCircle" className="h-14 w-14 text-primary/40" />
            <h4 className="text-base font-semibold">Sélectionnez une conversation</h4>
            <p className="max-w-sm text-sm text-muted-foreground">
              Choisissez un groupe, un canal de diffusion ou un message privé dans la liste, ou créez une nouvelle conversation.
            </p>
          </div>
        )}
      </div>
    </div>
  );
}

function conversationTitle(c: WorkplaceConversation, _membersCount: number): string {
  if (c.type === 'group' || c.type === 'channel') return c.name ?? 'Sans nom';
  const others = (c.participants ?? []).filter((p) => !p.role || p.role !== 'owner');
  return others.map((p) => p.user?.name ?? `#${p.user_id}`).join(', ') || 'Message privé';
}

function MessageBubble({ message }: { message: WorkplaceMessage }) {
  return (
    <div className="flex max-w-[75%] gap-2">
      <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-300 text-xs font-semibold text-white">
        {((message.user?.name ?? '?').slice(0, 2)).toUpperCase()}
      </span>
      <div>
        <div className="rounded-xl bg-muted px-3 py-2 text-sm">
          <strong className="text-xs">{message.user?.name}</strong>
          <p className="mt-0.5">{message.content}</p>
        </div>
        <small className="mt-1 block text-xs text-muted-foreground">{formatDateTime(message.created_at)}</small>
      </div>
    </div>
  );
}

function NewConversationForm({
  members,
  workplaceId,
  onCreated,
}: {
  members: { id: number; user_id: number; user?: { id: number; name: string } | null }[];
  workplaceId?: number;
  onCreated: (id: string) => void;
}) {
  const queryClient = useQueryClient();
  const [open, setOpen] = useState(false);
  const [type, setType] = useState<'group' | 'channel' | 'private'>('group');
  const [name, setName] = useState('');
  const [participants, setParticipants] = useState<number[]>([]);

  const create = useMutation({
    mutationFn: () =>
      createConversation({
        workplace_id: workplaceId as number,
        type,
        name: type === 'private' ? undefined : name,
        participant_ids: participants,
      }),
    onSuccess: (res) => {
      queryClient.invalidateQueries({ queryKey: ['workplace-conversations', workplaceId] });
      setOpen(false);
      setName('');
      setParticipants([]);
      onCreated(String(res.data.id));
    },
  });

  if (!open) {
    return (
      <button
        type="button"
        onClick={() => setOpen(true)}
        className="flex w-full items-center justify-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground"
      >
        <Icon name="plus" className="h-4 w-4" />
        Nouvelle conversation
      </button>
    );
  }

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        create.mutate();
      }}
      className="flex flex-col gap-2"
    >
      <select value={type} onChange={(e) => setType(e.target.value as typeof type)} className="rounded border border-border bg-background px-2 py-1 text-sm">
        <option value="group">Groupe</option>
        <option value="channel">Canal de diffusion</option>
        <option value="private">Message privé</option>
      </select>
      {type !== 'private' && (
        <input
          required
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Nom (ex. Projet Atlas, Annonces…)"
          className="rounded border border-border bg-background px-2 py-1 text-sm"
        />
      )}
      <select
        multiple
        size={4}
        value={participants.map(String)}
        onChange={(e) => setParticipants(Array.from(e.target.selectedOptions, (o) => Number(o.value)))}
        className="rounded border border-border bg-background px-2 py-1 text-sm"
      >
        {members.map((m) => (
          <option key={m.id} value={m.user_id}>
            {m.user?.name ?? `#${m.user_id}`}
          </option>
        ))}
      </select>
      <div className="flex gap-2">
        <button type="submit" disabled={create.isPending || workplaceId == null} className="flex-1 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-60">
          Créer
        </button>
        <button type="button" onClick={() => setOpen(false)} className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted">
          Annuler
        </button>
      </div>
      {create.isError && <p className="text-xs text-danger">La création a échoué.</p>}
    </form>
  );
}
