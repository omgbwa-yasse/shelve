'use client';

import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useWorkplace } from '@/features/workplaces/context';
import {
  createTopic,
  createTopicPost,
  listTopics,
  listTopicPosts,
} from '@/features/workplaces/services/workplace.service';
import { Icon } from '@/components/icons';
import { formatDate } from '@/utils/format-date';
import type { WorkplaceTopic, WorkplaceTopicPost } from '@/features/workplaces/types';

/**
 * Discussions d'un workplace (équivalent Alfresco Site Discussions) — liste des
 * sujets, détail d'un sujet avec ses réponses, création de sujet et de réponse.
 */
export default function WorkplaceTopicsPage() {
  const { code, workplace } = useWorkplace();
  const [search, setSearch] = useState('');
  const [showForm, setShowForm] = useState(false);
  const [title, setTitle] = useState('');
  const [body, setBody] = useState('');
  const [selectedTopic, setSelectedTopic] = useState<WorkplaceTopic | null>(null);
  const [reply, setReply] = useState('');
  const qc = useQueryClient();

  const { data: topicsData, isLoading } = useQuery({
    queryKey: ['workplace-topics', code, search],
    queryFn: () => listTopics(code, search || undefined),
    enabled: code.length > 0,
  });

  const topics = topicsData?.data ?? [];

  const createTopicMutation = useMutation({
    mutationFn: () => createTopic(code, { title, body }),
    onSuccess: () => {
      setShowForm(false);
      setTitle('');
      setBody('');
      qc.invalidateQueries({ queryKey: ['workplace-topics'] });
    },
  });

  const postMutation = useMutation({
    mutationFn: (content: string) => createTopicPost(code, selectedTopic!.id, content),
    onSuccess: () => {
      setReply('');
      qc.invalidateQueries({ queryKey: ['workplace-topic-posts'] });
    },
  });

  return (
    <div className="flex flex-col gap-4">
      <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border bg-surface p-3 shadow-sm">
        <div className="flex items-center gap-2">
          <Icon name="messagesSquare" className="h-5 w-5 text-indigo-600" />
          <h3 className="font-semibold">Discussions</h3>
        </div>
        <div className="flex flex-wrap items-center gap-2">
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Rechercher un sujet…"
            className="rounded border border-border bg-background px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
          />
          <button
            type="button"
            onClick={() => setShowForm((v) => !v)}
            className="flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            <Icon name="plus" className="h-3.5 w-3.5" />
            Nouveau sujet
          </button>
        </div>
      </div>

      {showForm && (
        <form
          onSubmit={(e) => {
            e.preventDefault();
            if (title.trim() && body.trim()) createTopicMutation.mutate();
          }}
          className="flex flex-col gap-3 rounded-xl border border-border bg-surface p-4 shadow-sm"
        >
          <input
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            placeholder="Titre du sujet"
            className="rounded border border-border bg-background px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
          />
          <textarea
            value={body}
            onChange={(e) => setBody(e.target.value)}
            placeholder="Contenu…"
            rows={4}
            className="rounded border border-border bg-background px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
          />
          <div className="flex justify-end gap-2">
            <button
              type="button"
              onClick={() => setShowForm(false)}
              className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              Annuler
            </button>
            <button
              type="submit"
              disabled={!title.trim() || !body.trim() || createTopicMutation.isPending}
              className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              Créer
            </button>
          </div>
        </form>
      )}

      {selectedTopic ? (
        <TopicDetail
          topic={selectedTopic}
          workplace={workplace?.name}
          onBack={() => setSelectedTopic(null)}
          reply={reply}
          setReply={setReply}
          onReply={() => reply.trim() && postMutation.mutate(reply)}
        />
      ) : isLoading ? (
        <p className="px-4 py-10 text-center text-sm text-muted-foreground">Chargement…</p>
      ) : topics.length === 0 ? (
        <div className="px-4 py-10 text-center">
          <Icon name="messagesSquare" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/20" />
          <p className="text-sm font-medium text-muted-foreground">Aucune discussion</p>
        </div>
      ) : (
        <ul className="divide-y divide-border overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
          {topics.map((topic) => (
            <li key={topic.id}>
              <button
                type="button"
                onClick={() => setSelectedTopic(topic)}
                className="flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-muted/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                <span className="mt-1 flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                  <Icon name="messagesSquare" className="h-4 w-4" />
                </span>
                <span className="min-w-0 flex-1">
                  <span className="flex flex-wrap items-center gap-2">
                    {topic.is_pinned && (
                      <span className="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-700">
                        Épinglé
                      </span>
                    )}
                    {topic.is_locked && (
                      <span className="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500">
                        Verrouillé
                      </span>
                    )}
                    <strong className="font-semibold">{topic.title}</strong>
                  </span>
                  <span className="mt-0.5 block truncate text-sm text-muted-foreground">{topic.body}</span>
                  <span className="mt-1 block text-xs text-muted-foreground">
                    {topic.user?.name ?? 'Utilisateur'} · {topic.posts_count ?? 0} réponse(s) ·{' '}
                    {formatDate(topic.updated_at ?? topic.created_at)}
                  </span>
                </span>
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}

function TopicDetail({
  topic,
  workplace,
  onBack,
  reply,
  setReply,
  onReply,
}: {
  topic: WorkplaceTopic;
  workplace?: string;
  onBack: () => void;
  reply: string;
  setReply: (v: string) => void;
  onReply: () => void;
}) {
  const { code } = useWorkplace();

  const { data, isLoading } = useQuery({
    queryKey: ['workplace-topic-posts', code, topic.id],
    queryFn: () => listTopicPosts(code, topic.id),
    enabled: code.length > 0,
  });

  const posts = data?.data ?? [];

  return (
    <div className="flex flex-col gap-4">
      <button
        type="button"
        onClick={onBack}
        className="flex w-fit items-center gap-1.5 rounded border border-border px-3 py-1.5 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
      >
        <Icon name="arrowRightSquare" className="h-4 w-4 rotate-180" />
        Retour aux discussions
      </button>

      <article className="rounded-xl border border-border bg-surface p-4 shadow-sm">
        <h3 className="font-semibold">{topic.title}</h3>
        <p className="mt-1 text-sm text-muted-foreground">
          {topic.user?.name} · {workplace}
        </p>
        <p className="mt-3 whitespace-pre-wrap text-sm">{topic.body}</p>
      </article>

      <section className="flex flex-col gap-3">
        {isLoading ? (
          <p className="py-6 text-center text-sm text-muted-foreground">Chargement…</p>
        ) : posts.length === 0 ? (
          <p className="py-6 text-center text-sm text-muted-foreground">Aucune réponse pour l'instant.</p>
        ) : (
          posts.map((post: WorkplaceTopicPost) => (
            <div key={post.id} className="rounded-xl border border-border bg-surface p-3 shadow-sm">
              <div className="flex items-center justify-between text-xs text-muted-foreground">
                <strong className="font-medium">{post.user?.name}</strong>
                <span>{formatDate(post.created_at)}</span>
              </div>
              <p className="mt-2 whitespace-pre-wrap text-sm">{post.body}</p>
            </div>
          ))
        )}

        {!topic.is_locked && (
          <form
            onSubmit={(e) => {
              e.preventDefault();
              onReply();
            }}
            className="flex gap-2"
          >
            <textarea
              value={reply}
              onChange={(e) => setReply(e.target.value)}
              placeholder="Votre réponse…"
              rows={2}
              className="flex-1 rounded border border-border bg-background px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
            />
            <button
              type="submit"
              disabled={!reply.trim()}
              className="self-end rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              Répondre
            </button>
          </form>
        )}
      </section>
    </div>
  );
}
