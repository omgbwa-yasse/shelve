'use client';

import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useWorkplace } from '@/features/workplaces/context';
import {
  createBlogPost,
  destroyBlogPost,
  listBlogPosts,
} from '@/features/workplaces/services/workplace.service';
import { Icon } from '@/components/icons';
import { formatDate } from '@/utils/format-date';
import type { WorkplaceBlogPost } from '@/features/workplaces/types';

/**
 * Blog d'un workplace (équivalent Alfresco Site Blog) — liste des billets
 * publiés, création de billets (brouillon ou publié), suppression par l'auteur.
 */
export default function WorkplaceBlogPage() {
  const { code } = useWorkplace();
  const [showForm, setShowForm] = useState(false);
  const [title, setTitle] = useState('');
  const [body, setBody] = useState('');
  const [status, setStatus] = useState<'draft' | 'published'>('published');
  const qc = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['workplace-blog-posts', code],
    queryFn: () => listBlogPosts(code),
    enabled: code.length > 0,
  });

  const posts = data?.data ?? [];

  const createMutation = useMutation({
    mutationFn: () => createBlogPost(code, { title, body, status }),
    onSuccess: () => {
      setShowForm(false);
      setTitle('');
      setBody('');
      setStatus('published');
      qc.invalidateQueries({ queryKey: ['workplace-blog-posts'] });
    },
  });

  const destroyMutation = useMutation({
    mutationFn: (postId: number) => destroyBlogPost(code, postId),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['workplace-blog-posts'] }),
  });

  return (
    <div className="flex flex-col gap-4">
      <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border bg-surface p-3 shadow-sm">
        <div className="flex items-center gap-2">
          <Icon name="newspaper" className="h-5 w-5 text-rose-600" />
          <h3 className="font-semibold">Blog</h3>
        </div>
        <button
          type="button"
          onClick={() => setShowForm((v) => !v)}
          className="flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          <Icon name="plus" className="h-3.5 w-3.5" />
          Nouveau billet
        </button>
      </div>

      {showForm && (
        <form
          onSubmit={(e) => {
            e.preventDefault();
            if (title.trim() && body.trim()) createMutation.mutate();
          }}
          className="flex flex-col gap-3 rounded-xl border border-border bg-surface p-4 shadow-sm"
        >
          <input
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            placeholder="Titre du billet"
            className="rounded border border-border bg-background px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
          />
          <textarea
            value={body}
            onChange={(e) => setBody(e.target.value)}
            placeholder="Contenu…"
            rows={5}
            className="rounded border border-border bg-background px-2.5 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
          />
          <div className="flex items-center justify-between gap-2">
            <select
              value={status}
              onChange={(e) => setStatus(e.target.value as 'draft' | 'published')}
              className="rounded border border-border bg-background px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary"
            >
              <option value="published">Publié</option>
              <option value="draft">Brouillon</option>
            </select>
            <div className="flex gap-2">
              <button
                type="button"
                onClick={() => setShowForm(false)}
                className="rounded border border-border px-3 py-1.5 text-sm hover:bg-muted focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                Annuler
              </button>
              <button
                type="submit"
                disabled={!title.trim() || !body.trim() || createMutation.isPending}
                className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
              >
                Publier
              </button>
            </div>
          </div>
        </form>
      )}

      {isLoading ? (
        <p className="px-4 py-10 text-center text-sm text-muted-foreground">Chargement…</p>
      ) : posts.length === 0 ? (
        <div className="px-4 py-10 text-center">
          <Icon name="newspaper" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/20" />
          <p className="text-sm font-medium text-muted-foreground">Aucun billet de blog</p>
        </div>
      ) : (
        <div className="flex flex-col gap-3">
          {posts.map((post: WorkplaceBlogPost) => (
            <article key={post.id} className="rounded-xl border border-border bg-surface p-4 shadow-sm">
              <div className="flex items-start justify-between gap-3">
                <div className="min-w-0">
                  <div className="flex flex-wrap items-center gap-2">
                    {post.status === 'draft' && (
                      <span className="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500">
                        Brouillon
                      </span>
                    )}
                    <h4 className="font-semibold">{post.title}</h4>
                  </div>
                  <p className="mt-0.5 text-xs text-muted-foreground">
                    {post.user?.name} · {formatDate(post.published_at ?? post.created_at)}
                  </p>
                </div>
                <button
                  type="button"
                  onClick={() => destroyMutation.mutate(post.id)}
                  title="Supprimer"
                  className="rounded p-1.5 text-muted-foreground hover:bg-red-50 hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                >
                  <Icon name="trash" className="h-4 w-4" />
                </button>
              </div>
              <p className="mt-3 whitespace-pre-wrap text-sm">{post.body}</p>
            </article>
          ))}
        </div>
      )}
    </div>
  );
}
