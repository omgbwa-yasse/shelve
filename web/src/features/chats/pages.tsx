'use client';

import { useEffect, useMemo, useRef, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useSearchParams } from 'next/navigation';
import { Icon } from '@/components/icons';
import { UniversalModal } from '@/components/ui/UniversalModal';
import { formatDateTime } from '@/utils/format-date';
import { usersApi } from '@/features/settings/services/setting.service';
import * as api from './services/chat.service';
import type { Entity } from '@/lib/api/types';
import type { WorkplaceConversation, WorkplaceMessage } from '@/features/workplaces/types';
import type { FeatureRoute } from '@/lib/routing';

const TYPE_META: Record<string, { icon: 'users' | 'messageCircle' | 'send'; className: string; label: string }> = {
  group: { icon: 'users', className: 'text-primary', label: 'Groupes' },
  channel: { icon: 'messageCircle', className: 'text-amber-500', label: 'Canaux de diffusion' },
  private: { icon: 'send', className: 'text-green-600', label: 'Messages privés' },
};

/**
 * Chat global — reproduit `chats/index.blade.php` :
 * liste des conversations de l'utilisateur (privées puis groupées par workplace),
 * fil de discussion, envoi de message, et modale « Nouveau message privé »
 * (ouverte par `?new=1`, ancre `#privates` sur la section messages privés).
 */
export function ChatList() {
  const queryClient = useQueryClient();
  const searchParams = useSearchParams();
  const [selectedId, setSelectedId] = useState<string | null>(searchParams.get('conv'));
  const [newOpen, setNewOpen] = useState(searchParams.get('new') === '1');

  // Ouvre la modale à l'arrivée sur `?new=1`, y compris lors d'une navigation
  // depuis le menu alors qu'on est déjà sur `/chats` (l'URL change sans remontage).
  useEffect(() => {
    if (searchParams.get('new') === '1') setNewOpen(true);
  }, [searchParams]);

  const { data: listData, isLoading: listLoading } = useQuery({
    queryKey: ['chats'],
    queryFn: api.listChats,
  });
  const conversations = listData?.data ?? [];

  const { data: selectedData, isLoading: threadLoading } = useQuery({
    queryKey: ['chat', selectedId],
    queryFn: () => api.getChat(selectedId as string),
    enabled: selectedId != null,
  });
  const selected = selectedData?.data ?? null;

  // Collègues (candidats au message privé) — le backend liste les utilisateurs de l'org.
  const { data: usersData } = useQuery({
    queryKey: ['users-for-chat'],
    queryFn: () => usersApi.list({ 'page.size': 200 } as never),
  });
  const colleagues = ((usersData?.data ?? []) as Entity[]).map((u) => ({
    id: Number(u.id),
    name: String(u.name ?? `#${u.id}`),
  }));

  // Garde le fil descendu sur les nouveaux messages.
  const threadRef = useRef<HTMLDivElement>(null);
  useEffect(() => {
    if (threadRef.current) threadRef.current.scrollTop = threadRef.current.scrollHeight;
  }, [selected?.messages?.length]);

  // Ancre #privates : positionne sur la section messages privés de la liste.
  const privatesRef = useRef<HTMLDivElement>(null);
  useEffect(() => {
    const scrollToPrivates = () => {
      if (window.location.hash === '#privates') {
        privatesRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    };
    scrollToPrivates();
    window.addEventListener('hashchange', scrollToPrivates);
    return () => window.removeEventListener('hashchange', scrollToPrivates);
  }, [conversations.length, searchParams]);

  const invalidateAll = () => {
    queryClient.invalidateQueries({ queryKey: ['chats'] });
    if (selectedId) queryClient.invalidateQueries({ queryKey: ['chat', selectedId] });
  };

  const send = useMutation({
    mutationFn: ({ conversationId, content }: { conversationId: string; content: string }) =>
      api.sendChatMessage(conversationId, content),
    onSuccess: invalidateAll,
  });

  const sendFile = useMutation({
    mutationFn: ({ conversationId, file }: { conversationId: string; file: File }) =>
      api.sendChatAttachment(conversationId, file),
    onSuccess: invalidateAll,
  });
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [filePending, setFilePending] = useState(false);

  const createChat = useMutation({
    mutationFn: ({ ids, name }: { ids: number[]; name: string }) => {
      const first = ids[0];
      return first != null && ids.length === 1
        ? api.createPrivateChat(first)
        : api.createGroupChat(ids, name);
    },
    onSuccess: (res) => {
      setNewOpen(false);
      const id = String(res.data.id);
      setSelectedId(id);
      invalidateAll();
    },
  });

  useEffect(() => {
    if (createChat.isError) window.alert('La création a échoué.');
  }, [createChat.isError]);

  // Groupement : privés d'abord, puis conversations par workplace.
  const groups = useMemo(() => {
    const privates = conversations.filter((c) => c.type === 'private');
    const byWorkplace = new Map<number, WorkplaceConversation[]>();
    const global = conversations.filter(
      (c) => c.type !== 'private' && (c.workplace_id == null || c.workplace_id === 0),
    );
    for (const c of conversations) {
      if (c.type === 'private') continue;
      if (c.workplace_id != null && c.workplace_id !== 0) {
        const list = byWorkplace.get(c.workplace_id) ?? [];
        list.push(c);
        byWorkplace.set(c.workplace_id, list);
      }
    }
    return { privates, global, byWorkplace };
  }, [conversations]);

  return (
    <div className="flex h-full min-h-[calc(100vh-140px)] flex-col gap-4 lg:flex-row">
      {/* ===================== LISTE DES CONVERSATIONS ===================== */}
      <aside className="w-full overflow-hidden rounded-xl border border-border bg-surface shadow-sm lg:w-64">
        <header className="flex items-center justify-between border-b border-border px-4 py-2.5">
          <span className="flex items-center gap-2 text-sm font-semibold">
            <Icon name="messageCircle" className="h-4 w-4 text-primary" />
            Chat
          </span>
          <button
            type="button"
            onClick={() => setNewOpen(true)}
            className="flex items-center gap-1 rounded bg-primary px-2 py-1 text-xs text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            <Icon name="plus" className="h-3.5 w-3.5" />
            Nouveau message
          </button>
        </header>
        <div className="max-h-[50vh] overflow-y-auto lg:max-h-none">
          <div ref={privatesRef} id="privates" className="flex items-center gap-1.5 bg-muted px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide text-muted-foreground">
            <Icon name="send" className="h-3 w-3 text-green-600" />
            Messages privés
          </div>
          {groups.privates.length === 0 ? (
            <p className="px-3 py-2 text-xs text-muted-foreground">Aucun message privé</p>
          ) : (
            groups.privates.map((c) => (
              <ConversationButton key={c.id} c={c} selectedId={selectedId} onClick={setSelectedId} />
            ))
          )}

          {groups.global.length > 0 && (
            <>
              <div className="flex items-center gap-1.5 bg-muted px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide text-muted-foreground">
                <Icon name="messageCircle" className="h-3 w-3 text-primary" />
                Conversations
              </div>
              {groups.global.map((c) => (
                <ConversationButton key={c.id} c={c} selectedId={selectedId} onClick={setSelectedId} />
              ))}
            </>
          )}

          {[...groups.byWorkplace.entries()].map(([wid, list]) => (
            <div key={wid}>
              <div className="flex items-center gap-1.5 bg-muted px-3 py-1.5 text-[11px] font-bold uppercase tracking-wide text-muted-foreground">
                <Icon name="briefcase" className="h-3 w-3 text-primary" />
                {String(list[0]?.workplace?.name ?? list[0]?.name ?? 'Workplace')}
              </div>
              {list.map((c) => (
                <ConversationButton key={c.id} c={c} selectedId={selectedId} onClick={setSelectedId} />
              ))}
            </div>
          ))}
          {conversations.length === 0 && !listLoading && (
            <p className="px-3 py-3 text-xs text-muted-foreground">
              Aucune conversation. Lancez un message privé à un collègue.
            </p>
          )}
          {listLoading && <p className="px-3 py-3 text-xs text-muted-foreground">Chargement…</p>}
        </div>
      </aside>

      {/* ===================== FIL DE DISCUSSION ===================== */}
      <div className="min-w-0 flex-1">
        {selected && selectedId ? (
          <section className="flex h-full flex-col overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
            <header className="flex items-center justify-between border-b border-border px-4 py-2.5">
              <div>
                <h4 className="flex items-center gap-2 text-sm font-semibold">
                  <Icon name={TYPE_META[selected.type]?.icon ?? 'messageCircle'} className={`h-4 w-4 ${TYPE_META[selected.type]?.className ?? ''}`} />
                  {conversationTitle(selected)}
                </h4>
                <p className="text-xs text-muted-foreground">
                  {selected.participants?.length ?? 0} membre(s)
                  {selected.description ? ` — ${selected.description}` : ''}
                </p>
              </div>
            </header>
            <div ref={threadRef} className="flex min-h-0 flex-1 flex-col gap-3 overflow-y-auto p-4">
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
                const input = e.currentTarget.elements.namedItem('content') as HTMLTextAreaElement;
                const content = input.value.trim();
                if (!content) return;
                send.mutate({ conversationId: selectedId, content });
                input.value = '';
              }}
              className="flex items-end gap-2 border-t border-border p-3"
            >
              <input
                ref={fileInputRef}
                type="file"
                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip"
                className="hidden"
                onChange={(e) => {
                  const file = e.target.files?.[0];
                  if (file && selectedId) {
                    setFilePending(true);
                    sendFile.mutate({ conversationId: selectedId, file }, {
                      onSettled: () => setFilePending(false),
                    });
                  }
                  e.target.value = '';
                }}
              />
              <button
                type="button"
                title="Joindre un fichier"
                disabled={sendFile.isPending || !selectedId}
                onClick={() => fileInputRef.current?.click()}
                className="flex h-9 w-9 shrink-0 items-center justify-center rounded border border-border bg-background text-muted-foreground hover:bg-muted disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                <Icon name="paperclip" className="h-4 w-4" />
              </button>
              <textarea
                name="content"
                rows={2}
                placeholder="Écrire un message…"
                className="flex-1 resize-none rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
              />
              <button
                type="submit"
                disabled={send.isPending || filePending}
                className="flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                <Icon name="send" className="h-4 w-4" />
                Envoyer
              </button>
            </form>
          </section>
        ) : (
          <div className="flex h-full min-h-[60vh] flex-col items-center justify-center gap-3 rounded-xl border border-border bg-surface text-center shadow-sm">
            <Icon name="messageCircle" className="h-14 w-14 text-primary/40" />
            <h4 className="text-base font-semibold">Bienvenue sur le Chat</h4>
            <p className="max-w-sm text-sm text-muted-foreground">
              Retrouvez ici toutes vos conversations de workplace et écrivez des messages privés à vos collègues.
            </p>
            <button type="button" onClick={() => setNewOpen(true)} className="flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
              <Icon name="plus" className="h-4 w-4" />
              Nouveau message
            </button>
          </div>
        )}
      </div>

      {newOpen && (
        <UniversalModal<{ value: string; label: string }>
          open
          onClose={() => setNewOpen(false)}
          title="Nouveau message"
          display="cards"
          rows={colleagues.map((c) => ({ value: String(c.id), label: c.name }))}
          rowKey={(c) => c.value}
          cardTitleField="label"
          multiple
          confirm
          searchable
          searchPlaceholder="Rechercher un collègue…"
          emptyLabel="Aucun collègue trouvé."
          onSelect={(sel) => {
            const ids = sel.map((s) => Number(s.value));
            if (ids.length === 0) return;
            const names = sel.map((s) => s.label);
            const name = ids.length === 1 ? '' : `Groupe (${names.join(', ')})`;
            createChat.mutate({ ids, name });
          }}
        />
      )}
    </div>
  );
}

function ConversationButton({
  c,
  selectedId,
  onClick,
}: {
  c: WorkplaceConversation;
  selectedId: string | null;
  onClick: (id: string) => void;
}) {
  const meta = TYPE_META[c.type] ?? TYPE_META.group!;
  return (
    <button
      type="button"
      onClick={() => onClick(String(c.id))}
      className={`flex w-full items-center gap-2 px-3 py-2 text-left text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary ${selectedId === String(c.id) ? 'bg-primary/10 text-primary' : ''}`}
    >
      <Icon name={meta.icon} className={`h-4 w-4 shrink-0 ${meta.className}`} />
      <span className="truncate">{conversationTitle(c)}</span>
    </button>
  );
}

function conversationTitle(c: WorkplaceConversation): string {
  if (c.type === 'group' || c.type === 'channel') return c.name ?? 'Sans nom';
  const others = (c.participants ?? []).filter((p) => !p.role || p.role !== 'owner');
  return others.map((p) => p.user?.name ?? `#${p.user_id}`).join(', ') || 'Message privé';
}

function MessageBubble({ message }: { message: WorkplaceMessage }) {
  const isImage = (message.attachment_mime ?? '').startsWith('image/');
  const hasAttachment = Boolean(message.attachment_name);
  return (
    <div className="flex max-w-[75%] gap-2">
      <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-300 text-xs font-semibold text-white">
        {((message.user?.name ?? '?').slice(0, 2)).toUpperCase()}
      </span>
      <div>
        <div className="rounded-xl bg-muted px-3 py-2 text-sm">
          <strong className="text-xs">{message.user?.name}</strong>
          {message.content && <p className="mt-0.5 whitespace-pre-wrap">{message.content}</p>}
          {hasAttachment && (
            <a
              href={api.chatAttachmentUrl(message.conversation_id, message.id)}
              target="_blank"
              rel="noreferrer"
              className="mt-1.5 flex items-center gap-2 rounded-lg border border-border bg-background p-2 text-xs hover:bg-muted"
            >
              {isImage ? (
                <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-primary/10 text-primary">
                  <Icon name="image" className="h-5 w-5" />
                </span>
              ) : (
                <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded bg-primary/10 text-primary">
                  <Icon name="paperclip" className="h-5 w-5" />
                </span>
              )}
              <span className="min-w-0">
                <span className="block truncate font-medium">{message.attachment_name}</span>
                <span className="block text-[11px] text-muted-foreground">
                  {message.attachment_size ? formatFileSize(message.attachment_size) : ''}
                </span>
              </span>
            </a>
          )}
        </div>
        <small className="mt-1 block text-xs text-muted-foreground">{formatDateTime(message.created_at)}</small>
      </div>
    </div>
  );
}

function formatFileSize(bytes: number): string {
  if (bytes < 1024) return `${bytes} o`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} Ko`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} Mo`;
}

export const routes: FeatureRoute[] = [
  { path: '/chats', List: ChatList },
];
