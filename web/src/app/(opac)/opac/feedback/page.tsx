'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useQuery } from '@tanstack/react-query';
import { apiFetch } from '@/lib/api/client';
import { Icon } from '@/components/icons';

/**
 * Formulaire de contact / feedback du portail public — reproduit
 * `opac/feedback/create.blade.php`. La soumission passe par `/api/public/feedbacks`
 * (usager public connecté : `user_id` déduit du token).
 */
export default function OpacFeedbackPage() {
  const [form, setForm] = useState({ subject: '', message: '', category: 'general' });
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  // Tente de détecter une session publique : un appel au historique feedback
  // renvoie 401 si l'usager n'est pas connecté.
  const { data, isLoading } = useQuery({
    queryKey: ['opac-feedback-auth-check'],
    queryFn: async () => {
      try {
        await apiFetch('/api/public/feedbacks');
        return true;
      } catch {
        return false;
      }
    },
    retry: false,
  });

  const isAuthed = data === true;

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    setError(null);
    setLoading(true);
    try {
      const res = await fetch('/api/proxy/api/public/feedbacks', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
      const json = await res.json().catch(() => null);
      if (!res.ok) {
        if (res.status === 401) {
          setError('Vous devez être connecté pour envoyer un message.');
        } else {
          const first = json?.errors ? Object.values(json.errors)[0] : json?.message;
          setError(first ?? 'Envoi refusé.');
        }
        setLoading(false);
        return;
      }
      setSuccess(true);
      setForm({ subject: '', message: '', category: 'general' });
    } catch {
      setError('Impossible de contacter le serveur.');
    }
    setLoading(false);
  }

  const inputCls = 'rounded-lg border border-border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary';

  if (isLoading) {
    return (
      <div className="mx-auto max-w-md px-4 py-16 text-center text-sm text-muted-foreground">
        Vérification de la session…
      </div>
    );
  }

  if (!isAuthed) {
    return (
      <div className="mx-auto max-w-md px-4 py-16 text-center">
        <Icon name="send" className="mx-auto mb-3 h-10 w-10 text-muted-foreground/30" />
        <h1 className="font-serif text-xl font-bold">Nous contacter</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          Pour envoyer un message, connectez-vous à votre espace lecteur.
        </p>
        <Link href="/opac/auth/login" className="mt-4 inline-block rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
          Se connecter
        </Link>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-2xl px-4 py-12">
      <div className="rounded-xl border border-border bg-surface p-8 shadow-sm">
        <h1 className="font-serif text-xl font-bold">Nous contacter</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Une question, un retour, une suggestion ? Écrivez-nous.
        </p>

        {success && (
          <p className="mt-4 flex items-center gap-2 rounded-lg bg-success/10 px-3 py-2 text-sm text-success">
            <Icon name="checkCircle" className="h-4 w-4 shrink-0" />
            Message envoyé avec succès. Merci !
          </p>
        )}
        {error && (
          <p className="mt-4 flex items-center gap-2 rounded-lg bg-danger/10 px-3 py-2 text-sm text-danger">
            <Icon name="alertTriangle" className="h-4 w-4 shrink-0" />
            {error}
          </p>
        )}

        <form onSubmit={submit} className="mt-6 flex flex-col gap-4">
          <label className="flex flex-col gap-1 text-sm">
            <span className="font-medium">Sujet</span>
            <input
              type="text"
              required
              value={form.subject}
              onChange={(e) => setForm((p) => ({ ...p, subject: e.target.value }))}
              placeholder="Objet de votre message"
              className={inputCls}
            />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="font-medium">Catégorie</span>
            <select
              value={form.category}
              onChange={(e) => setForm((p) => ({ ...p, category: e.target.value }))}
              className={inputCls}
            >
              <option value="general">Question générale</option>
              <option value="search">Recherche de documents</option>
              <option value="suggestion">Suggestion</option>
              <option value="problem">Signalement d&apos;un problème</option>
            </select>
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span className="font-medium">Message</span>
            <textarea
              required
              rows={6}
              value={form.message}
              onChange={(e) => setForm((p) => ({ ...p, message: e.target.value }))}
              placeholder="Votre message…"
              className={inputCls}
            />
          </label>
          <button
            type="submit"
            disabled={loading}
            className="mt-2 inline-flex items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            <Icon name="send" className="h-4 w-4" />
            {loading ? 'Envoi…' : 'Envoyer'}
          </button>
        </form>
      </div>
    </div>
  );
}
